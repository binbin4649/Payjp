<?php

declare(strict_types=1);

namespace Payjp\Service;

use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Payjp\Model\Entity\PayjpCharge;
use Payjp\Model\Entity\PayjpUser;
use Payjp\Model\Table\PayjpChargesTable;
use Payjp\Model\Table\PayjpUsersTable;
use Point\Service\PointService;
use Throwable;

/**
 * PAY.JP 顧客管理・決済実行の公開 API。
 *
 * Checkout Session 作成（カード登録 / 都度課金）・off-session オートチャージ・webhook / リダイレクト
 * による完了確定の各メソッドが入口となり、内部で PayjpApiService 経由の PAY.JP 通信、payjp_users /
 * payjp_charges の記録、PointService::charge() 呼び出しを一貫して行う。
 */
class PayjpService
{
    private PayjpApiService $api;

    /**
     * @var \Payjp\Model\Table\PayjpUsersTable
     */
    private PayjpUsersTable $payjpUsers;

    /**
     * @var \Payjp\Model\Table\PayjpChargesTable
     */
    private PayjpChargesTable $payjpCharges;

    /**
     * @param \Payjp\Service\PayjpApiService|null $api PAY.JP API ラッパー（テストでモック注入）。
     */
    public function __construct(?PayjpApiService $api = null)
    {
        $this->api = $api ?? new PayjpApiService();
        $this->payjpUsers = TableRegistry::getTableLocator()->get('Payjp.PayjpUsers');
        $this->payjpCharges = TableRegistry::getTableLocator()->get('Payjp.PayjpCharges');
    }

    /**
     * UUID ベースの冪等性キーを生成する。
     */
    public function generateIdempotencyKey(): string
    {
        return Text::uuid();
    }

    /**
     * オートチャージ用カード登録。mode=setup の Checkout Session を作成し payjp_users を仮登録する。
     *
     * @param int $userId 対象ユーザーID。
     * @param int $autoChargeAmount オートチャージ課金額（円）。
     * @param array<string, mixed> $options success_url / cancel_url / point（オートチャージ実行時の加算ポイント。省略時は amount と同額）/ payment_method_types（['card'] 等。省略時は未送信）等。
     * @return string|false リダイレクト URL、失敗時 false。
     */
    public function createSetupCheckout(int $userId, int $autoChargeAmount, array $options = []): string|false
    {
        try {
            $email = $this->userEmail($userId);
            $customerId = $this->resolveSetupCustomerId($userId, $email);
            if ($customerId === false) {
                return false;
            }

            $sessionParams = [
                'mode' => 'setup',
                'user_id' => $userId,
                'customer_id' => $customerId,
                'success_url' => $options['success_url'] ?? null,
                'cancel_url' => $options['cancel_url'] ?? null,
                'idempotency_key' => $this->generateIdempotencyKey(),
            ];
            if ($email !== null) {
                $sessionParams['customer_email'] = $email;
            }
            if (!empty($options['payment_method_types']) && is_array($options['payment_method_types'])) {
                $sessionParams['payment_method_types'] = array_values($options['payment_method_types']);
            }
            $result = $this->api->createCheckoutSession($sessionParams);
            if ($result === false) {
                return false;
            }

            // 確定前の仮登録（status は active ではない / PaymentMethod 未保存）
            $user = $this->payjpUsers->newEntity([
                'user_id' => $userId,
                'payjp_customer_code' => $customerId,
                // 確定状態のポーリングと cron の照合でこの行を特定するため cs_ を持たせる
                'payjp_checkout_session_code' => $result['id'] ?? null,
                'status' => 'inactive',
                'type' => 'auto_charge',
                'auto_charge_amount' => $autoChargeAmount,
                // オートチャージ実行時の加算ポイント。メインアプリがボーナス込みプラン等で
                // 決済金額と異なる値を指定できる。null は amount と同額を加算（後方互換）。
                'auto_charge_point' => isset($options['point']) ? (int)$options['point'] : null,
            ]);
            if (!$this->payjpUsers->save($user)) {
                return false;
            }

            return $result['url'];
        } catch (Throwable $e) {
            Log::error('PayjpService::createSetupCheckout failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * setup 用の顧客 ID（cus_...）を決める。
     *
     * 現行行に cus_ があれば再利用し、なければ metadata.user_id 付きで新規作成する。
     * 旧データ（Users.id を顧客 ID にしたもの等）は再利用しない。
     *
     * @param int $userId 対象ユーザーID。
     * @param string|null $email メール。
     * @return string|false
     */
    private function resolveSetupCustomerId(int $userId, ?string $email): string|false
    {
        $current = $this->payjpUsers->find('currentByUser', userId: $userId)->first();
        $existing = $current?->payjp_customer_code;
        if (is_string($existing) && str_starts_with($existing, 'cus_')) {
            return $existing;
        }

        return $this->api->createCustomer($email, ['user_id' => (string)$userId]);
    }

    /**
     * 都度課金。mode=payment の Checkout Session を作成し payjp_charges を pending で記録する。
     *
     * @param int $userId 対象ユーザーID。
     * @param int $amount 課金額（円）。
     * @param array<string, mixed> $options success_url / cancel_url / point（加算ポイント。省略時は amount と同額）/ payment_method_types（['card'] / ['paypay'] 等。省略時は未送信）等。
     * @return string|false リダイレクト URL、失敗時 false。
     */
    public function createPaymentCheckout(int $userId, int $amount, array $options = []): string|false
    {
        try {
            $key = $this->generateIdempotencyKey();
            $sessionParams = [
                'mode' => 'payment',
                'amount' => $amount,
                'user_id' => $userId,
                'success_url' => $options['success_url'] ?? null,
                'cancel_url' => $options['cancel_url'] ?? null,
                'idempotency_key' => $key,
            ];
            $email = $this->userEmail($userId);
            if ($email !== null) {
                $sessionParams['customer_email'] = $email;
            }
            if (!empty($options['payment_method_types']) && is_array($options['payment_method_types'])) {
                $sessionParams['payment_method_types'] = array_values($options['payment_method_types']);
            }
            $result = $this->api->createCheckoutSession($sessionParams);
            if ($result === false) {
                return false;
            }

            $charge = $this->payjpCharges->newEntity([
                'user_id' => $userId,
                'point_book_id' => null,
                'status' => 'pending',
                'type' => 'one_time',
                'amount' => $amount,
                // 加算ポイント。メインアプリがボーナス付きプラン等で決済金額と異なる値を指定できる。
                // null の場合は確定時に amount と同額を加算する（後方互換）。
                'point' => isset($options['point']) ? (int)$options['point'] : null,
                'payjp_checkout_session_code' => $result['id'],
                'idempotency_key' => $key,
            ]);
            if (!$this->payjpCharges->save($charge)) {
                return false;
            }

            return $result['url'];
        } catch (Throwable $e) {
            Log::error('PayjpService::createPaymentCheckout failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * オートチャージ。登録済み顧客の情報と auto_charge_amount で off-session 課金を実行する。
     *
     * @param int $userId 対象ユーザーID。
     * @return \Payjp\Model\Entity\PayjpCharge|false 成功時 PayjpCharge、失敗時 false。
     */
    public function chargeAuto(int $userId): PayjpCharge|false
    {
        $user = $this->findChargeableUser($userId);
        if ($user === null) {
            return false;
        }

        $amount = (int)$user->auto_charge_amount;
        $key = $this->generateIdempotencyKey();

        try {
            $result = $this->api->createPaymentFlow(
                $amount,
                (string)$user->payjp_customer_code,
                (string)$user->payjp_payment_method_code,
                $key,
            );
        } catch (Throwable $e) {
            // 通信・処理例外 → status=failure
            $this->recordFailureCharge($user, $amount, $key, null, $e->getMessage());
            $user->status = 'failure';
            $user->log = $e->getMessage();
            $this->payjpUsers->save($user);

            return false;
        }

        if (($result['status'] ?? '') === 'succeeded') {
            $charge = $this->payjpCharges->newEntity([
                'user_id' => $user->user_id,
                'point_book_id' => null,
                'status' => 'success',
                'type' => 'auto_charge',
                'amount' => $amount,
                'point' => $user->auto_charge_point !== null ? (int)$user->auto_charge_point : null,
                'payjp_status' => $result['status'],
                'payjp_customer_code' => $user->payjp_customer_code,
                'payjp_payment_flow_code' => $result['id'] ?? null,
                'payjp_payment_method_code' => $result['payment_method_id'] ?? $user->payjp_payment_method_code,
                'card_brand' => $result['card_brand'] ?? $user->card_brand,
                'card_last4' => $result['card_last4'] ?? $user->card_last4,
                'idempotency_key' => $key,
            ]);
            $this->payjpCharges->save($charge);

            $pointBook = (new PointService())->charge($user->user_id, (int)($user->auto_charge_point ?? $amount), [
                'app_name' => 'Payjp',
                'charge_type' => 'payjp',
                'foreign_model' => 'PayjpCharge',
                'foreign_id' => $charge->id,
            ]);
            if ($pointBook) {
                $charge->point_book_id = $pointBook->id;
                $this->payjpCharges->save($charge);
            }

            $user->status = 'active';
            $user->last_synced = new DateTime();
            $this->payjpUsers->save($user);

            return $charge;
        }

        // canceled / requires_action / failed 等 → 決済失敗の遷移
        $this->recordFailureCharge($user, $amount, $key, $result, 'auto charge failed: ' . ($result['status'] ?? 'unknown'));
        $user->status = $user->status === 'suspended' ? 'inactive' : 'suspended';
        $user->last_synced = new DateTime();
        $this->payjpUsers->save($user);

        return false;
    }

    /**
     * 残ポイントが auto_charge_amount を下回っている場合のみオートチャージを実行する。
     *
     * カード登録完了直後にメインアプリ／コントローラーから呼び出すことを想定した補助フロー。
     * 「いつ呼ぶか」はメインアプリの責務だが、「残高 < auto_charge_amount なら課金する」機構を
     * Payjp が提供する。残高判定は PointService::getBalance() 経由で行う。
     *
     * @param int $userId 対象ユーザーID。
     * @return \Payjp\Model\Entity\PayjpCharge|false 課金した場合 PayjpCharge、不要・対象外・残高不明時 false。
     */
    public function chargeAutoIfBelow(int $userId): PayjpCharge|false
    {
        $user = $this->findChargeableUser($userId);
        if ($user === null) {
            return false;
        }

        $balance = (new PointService())->getBalance($userId);
        if ($balance === null) {
            return false;
        }

        if ($balance >= (int)$user->auto_charge_amount) {
            return false;
        }

        return $this->chargeAuto($userId);
    }

    /**
     * ユーザー退会処理。顧客を削除し payjp_users を deleted にする。
     *
     * @param int $userId 対象ユーザーID。
     * @return \Payjp\Model\Entity\PayjpUser|false 成功時 PayjpUser、失敗時 false。
     */
    public function deleteCustomer(int $userId): PayjpUser|false
    {
        // 現在の登録カード行を対象にする（複数行ある場合に旧行を拾わない）
        $user = $this->payjpUsers->find('currentByUser', userId: $userId)->first();
        if ($user === null) {
            return false;
        }

        try {
            $deleted = $this->api->deleteCustomer((string)$user->payjp_customer_code);
        } catch (Throwable $e) {
            $user->status = 'failure';
            $user->log = $e->getMessage();
            $this->payjpUsers->save($user);

            return false;
        }

        if ($deleted !== true) {
            return false;
        }

        $user->status = 'deleted';
        $user->last_synced = new DateTime();
        if (!$this->payjpUsers->save($user)) {
            return false;
        }

        return $user;
    }

    /**
     * 当月（または指定月）にカード有効期限を迎えるユーザーへ期限切れ予告メールを送る。
     *
     * cron 起動の CardDeadlineCommand から呼び出す想定。実行前に card_deadline 未保存の
     * 既存行を PAY.JP から補完（バックフィル）してから抽出する。送信済みガードは
     * change_logs（model_name=Users, action=card_deadline, created >= 対象月1日）で行い、
     * 同月内の再送を防ぐ。送信は Member の EmailTemplate（mail_type=card_deadline）を使用し、
     * 送信後に ChangeLogsTable::commonLog で記録する。1ユーザーの失敗は他ユーザーへ波及させない。
     *
     * @param \Cake\I18n\Date|null $targetMonth 対象月（月内の任意の日付。省略時は当月）。
     * @return array{sent: int, skipped: int, failed: int}
     */
    public function notifyCardDeadline(?Date $targetMonth = null): array
    {
        $targetMonth = $targetMonth ?? Date::today();
        $result = ['sent' => 0, 'skipped' => 0, 'failed' => 0];

        $this->backfillCardDeadline();

        $locator = TableRegistry::getTableLocator();
        $emailTemplate = $locator->get('Member.EmailTemplates')->getByMailType('card_deadline');
        if ($emailTemplate === null) {
            Log::warning('PayjpService::notifyCardDeadline skipped: mail_type=card_deadline の有効なテンプレートがありません');

            return $result;
        }

        $messages = $locator->get('Member.Messages');
        $changeLogs = $locator->get('Member.ChangeLogs');
        $monthStart = new DateTime($targetMonth->firstOfMonth()->format('Y-m-d 00:00:00'));

        $rows = $this->payjpUsers->find('expiringInMonth', month: $targetMonth)->contain(['Users'])->all();
        foreach ($rows as $row) {
            try {
                if (empty($row->user->email)) {
                    $result['skipped']++;
                    continue;
                }
                $notified = $changeLogs->exists([
                    'ChangeLogs.model_name' => 'Users',
                    'ChangeLogs.record_id' => (int)$row->user_id,
                    'ChangeLogs.action' => 'card_deadline',
                    'ChangeLogs.created >=' => $monthStart,
                ]);
                if ($notified) {
                    $result['skipped']++;
                    continue;
                }
                $messages->sendMailRightNow($row->user, $emailTemplate->id);
                $changeLogs->commonLog('Users', (int)$row->user_id, 'card_deadline');
                $result['sent']++;
            } catch (Throwable $e) {
                Log::error(sprintf(
                    'PayjpService::notifyCardDeadline failed (user_id=%s): %s',
                    $row->user_id,
                    $e->getMessage(),
                ));
                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * card_deadline 未保存の既存行を PAY.JP の PaymentMethod 情報から補完する。
     *
     * card_deadline カラム追加以前に登録されたカードを通知対象に含めるための自己回復処理。
     * 取得失敗（API エラー・exp 未設定）は cardDeadline() 内で warning ログ済みのためスキップする。
     *
     * @return void
     */
    private function backfillCardDeadline(): void
    {
        $rows = $this->payjpUsers->find()
            ->where([
                'PayjpUsers.status' => 'active',
                'PayjpUsers.payjp_payment_method_code IS NOT' => null,
                'PayjpUsers.card_deadline IS' => null,
            ])
            ->all();
        foreach ($rows as $row) {
            $deadline = $this->api->cardDeadline((string)$row->payjp_payment_method_code);
            if ($deadline === null) {
                continue;
            }
            $row->card_deadline = $deadline;
            $this->payjpUsers->save($row);
        }
    }

    /**
     * PAY.JP の webhook イベントを受けて payjp_charges / payjp_users を確定する。
     *
     * @param array<string, mixed> $event PAY.JP webhook イベント。
     * @return bool 処理した場合 true。
     */
    public function handleWebhook(array $event): bool
    {
        $type = (string)($event['type'] ?? '');
        $data = (array)($event['data'] ?? []);
        $mode = $data['mode'] ?? null;

        // カード登録（setup）完了
        if ($type === 'checkout.session.completed' && $mode === 'setup') {
            return $this->confirmSetup($data);
        }

        // 都度課金（one_time）成功
        if ($type === 'payment_flow.succeeded' || ($type === 'checkout.session.completed' && $mode === 'payment')) {
            return $this->confirmCharge($data);
        }

        // 失敗系
        if (str_contains($type, 'failed') || str_contains($type, 'canceled')) {
            return $this->failCharge($data, $type);
        }

        return false;
    }

    /**
     * Webhook の event id を受け、PAY.JP から正本を再取得して確定する。
     *
     * 生ペイロードは信用せず、getEvent() で再取得・正規化したイベントを handleWebhook() に委譲する。
     * 二重確定は handleWebhook() / confirmChargeEntity() の冪等ガードが防ぐ。
     *
     * @param string $eventId PAY.JP イベント ID。
     * @return bool 処理した場合 true。
     */
    public function handleWebhookById(string $eventId): bool
    {
        if ($eventId === '') {
            return false;
        }
        $event = $this->api->getEvent($eventId);
        if ($event === false) {
            return false;
        }

        return $this->handleWebhook($event);
    }

    /**
     * success_url 到達時等に getCheckoutSession で成否を確認し確定する補助経路。
     *
     * @param string $checkoutSessionId Checkout Session ID（cs_...）。
     * @return \Payjp\Model\Entity\PayjpCharge|\Payjp\Model\Entity\PayjpUser|false
     */
    public function completeCheckout(string $checkoutSessionId): PayjpCharge|PayjpUser|false
    {
        try {
            $session = $this->api->getCheckoutSession($checkoutSessionId);
        } catch (Throwable $e) {
            Log::error('PayjpService::completeCheckout failed: ' . $e->getMessage());

            return false;
        }
        if ($session === false) {
            return false;
        }

        if (!in_array($session['status'] ?? '', ['completed', 'complete'], true)) {
            return false;
        }

        $mode = $session['mode'] ?? null;
        if ($mode === 'payment') {
            $charge = $this->payjpCharges->find('byCheckoutSession', sessionId: $checkoutSessionId)->first();
            if ($charge === null) {
                return false;
            }

            return $this->confirmChargeEntity($charge, $session);
        }

        if ($mode === 'setup') {
            $userId = $session['user_id'] ?? null;
            if ($userId === null) {
                return false;
            }

            return $this->confirmSetup($session) ? $this->latestUser((int)$userId) : false;
        }

        return false;
    }

    /**
     * Checkout Session の確定状態を**自社 DB だけ**から返す（webhook 受信の検知用）。
     *
     * 画面のローディングから 2 秒間隔で呼ばれるため、PAY.JP API は叩かない。
     * 確定の正本は webhook（handleWebhook）で、このメソッドはその結果を読むだけ。
     *
     * @param string $checkoutSessionId Checkout Session ID（cs_...）。
     * @param int $userId 問い合わせ元ユーザーID（他人のセッションを覗かせないための照合）。
     * @return array{state: string, kind: string|null} state は pending / success / failure。
     */
    public function checkoutStatus(string $checkoutSessionId, int $userId): array
    {
        $pending = ['state' => 'pending', 'kind' => null];
        if ($checkoutSessionId === '') {
            return $pending;
        }

        $charge = $this->payjpCharges->find('byCheckoutSession', sessionId: $checkoutSessionId)->first();
        if ($charge !== null) {
            // 他ユーザーのセッション ID を投げられても状態を漏らさない
            if ((int)$charge->user_id !== $userId) {
                return $pending;
            }

            return [
                'state' => match ($charge->status) {
                    'success' => 'success',
                    'failure' => 'failure',
                    default => 'pending',
                },
                'kind' => 'payment',
            ];
        }

        $user = $this->payjpUsers->find('byCheckoutSession', sessionId: $checkoutSessionId)->first();
        if ($user !== null) {
            if ((int)$user->user_id !== $userId) {
                return $pending;
            }
            $confirmed = $user->status === 'active' && !empty($user->payjp_payment_method_code);

            return [
                'state' => match (true) {
                    $confirmed => 'success',
                    $user->status === 'failure' => 'failure',
                    default => 'pending',
                },
                'kind' => 'setup',
            ];
        }

        // 記録が見つからない場合は pending 扱い（cron の照合に委ねる）
        return $pending;
    }

    /**
     * 確定していない Checkout を PAY.JP と照合して確定する（webhook 未着の回収）。
     *
     * success_url に戻ってこなかった／webhook が届かなかったケースを cron から拾う。
     * 確定処理・冪等ガードは completeCheckout() に委ねる。1 件の失敗は他へ波及させない。
     *
     * @param int $withinHours 対象とする作成日時の遡り時間（既定 24 時間）。
     * @return array{checked: int, confirmed: int, failed: int}
     */
    public function syncPendingCheckouts(int $withinHours = 24): array
    {
        $since = new DateTime('-' . max(1, $withinHours) . ' hours');
        $result = ['checked' => 0, 'confirmed' => 0, 'failed' => 0];

        $sessionIds = $this->payjpCharges->find()
            ->select(['payjp_checkout_session_code'])
            ->where([
                'PayjpCharges.status' => 'pending',
                'PayjpCharges.payjp_checkout_session_code IS NOT' => null,
                'PayjpCharges.created >=' => $since,
            ])
            ->all()
            ->extract('payjp_checkout_session_code')
            ->toList();

        // 仮登録のまま（PaymentMethod 未保存）のカード登録行
        $setupIds = $this->payjpUsers->find()
            ->select(['payjp_checkout_session_code'])
            ->where([
                'PayjpUsers.payjp_checkout_session_code IS NOT' => null,
                'PayjpUsers.payjp_payment_method_code IS' => null,
                'PayjpUsers.created >=' => $since,
            ])
            ->all()
            ->extract('payjp_checkout_session_code')
            ->toList();

        foreach (array_unique([...$sessionIds, ...$setupIds]) as $sessionId) {
            $result['checked']++;
            try {
                if ($this->completeCheckout((string)$sessionId) !== false) {
                    $result['confirmed']++;
                }
            } catch (Throwable $e) {
                $result['failed']++;
                Log::error('PayjpService::syncPendingCheckouts failed session=' . $sessionId . ': ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * webhook データから pending / 既存 charge を解決する。
     *
     * 解決順: cs_ id → checkout_session_id → metadata.idempotency_key → payment_flow_id。
     *
     * @param array<string, mixed> $data イベントデータ（正規化済み）。
     * @return \Payjp\Model\Entity\PayjpCharge|null
     */
    private function findChargeForWebhookData(array $data): ?PayjpCharge
    {
        $id = (string)($data['id'] ?? '');
        if (str_starts_with($id, 'cs_')) {
            $charge = $this->payjpCharges->find('byCheckoutSession', sessionId: $id)->first();
            if ($charge !== null) {
                return $charge;
            }
        }

        $checkoutSessionId = (string)($data['checkout_session_id'] ?? '');
        if (str_starts_with($checkoutSessionId, 'cs_')) {
            $charge = $this->payjpCharges->find('byCheckoutSession', sessionId: $checkoutSessionId)->first();
            if ($charge !== null) {
                return $charge;
            }
        }

        $metadata = (array)($data['metadata'] ?? []);
        $idempotencyKey = (string)($data['idempotency_key'] ?? ($metadata['idempotency_key'] ?? ''));
        if ($idempotencyKey !== '') {
            $charge = $this->payjpCharges->find('byIdempotencyKey', idempotencyKey: $idempotencyKey)->first();
            if ($charge !== null) {
                return $charge;
            }
        }

        $paymentFlowId = (string)($data['payment_flow_id'] ?? '');
        if ($paymentFlowId === '' && $id !== '' && !str_starts_with($id, 'cs_')) {
            $paymentFlowId = $id;
        }
        if ($paymentFlowId !== '') {
            $charge = $this->payjpCharges->find('byPaymentFlow', paymentFlowId: $paymentFlowId)->first();
            if ($charge !== null) {
                return $charge;
            }
        }

        Log::warning('PayjpService::findChargeForWebhookData: charge not found id=' . $id
            . ' checkout_session_id=' . $checkoutSessionId
            . ' idempotency_key=' . $idempotencyKey
            . ' payment_flow_id=' . $paymentFlowId);

        return null;
    }

    /**
     * 都度課金の成功確定（webhook 経路）。
     *
     * @param array<string, mixed> $data イベントデータ。
     * @return bool
     */
    private function confirmCharge(array $data): bool
    {
        $charge = $this->findChargeForWebhookData($data);
        if ($charge === null) {
            return false;
        }

        $this->confirmChargeEntity($charge, $data);

        return true;
    }

    /**
     * 取得済みの pending charge を success に確定し PointService でポイント加算する。
     *
     * @param \Payjp\Model\Entity\PayjpCharge $charge 対象 charge。
     * @param array<string, mixed> $data 確定情報（webhook data / checkout session）。
     * @return \Payjp\Model\Entity\PayjpCharge
     */
    private function confirmChargeEntity(PayjpCharge $charge, array $data): PayjpCharge
    {
        // すでに確定済みなら二重課金しない
        if ($charge->status === 'success') {
            return $charge;
        }

        $charge->status = 'success';
        $charge->payjp_status = $data['status'] ?? 'succeeded';
        if (!empty($data['payment_flow_id'])) {
            $charge->payjp_payment_flow_code = $data['payment_flow_id'];
        }
        if (!empty($data['payment_method_id'])) {
            $charge->payjp_payment_method_code = $data['payment_method_id'];
        }
        if (isset($data['card_brand'])) {
            $charge->card_brand = $data['card_brand'];
        }
        if (isset($data['card_last4'])) {
            $charge->card_last4 = $data['card_last4'];
        }
        $this->payjpCharges->save($charge);

        $pointBook = (new PointService())->charge((int)$charge->user_id, (int)($charge->point ?? $charge->amount), [
            'app_name' => 'Payjp',
            'charge_type' => 'payjp',
            'foreign_model' => 'PayjpCharge',
            'foreign_id' => $charge->id,
        ]);
        if ($pointBook) {
            $charge->point_book_id = $pointBook->id;
            $this->payjpCharges->save($charge);
        }

        return $charge;
    }

    /**
     * カード登録（setup）完了確定。payjp_users を active にし PaymentMethod / Customer を保存する。
     *
     * @param array<string, mixed> $data イベントデータ / checkout session。
     * @return bool
     */
    private function confirmSetup(array $data): bool
    {
        $userId = $data['user_id'] ?? null;
        if (empty($userId)) {
            return false;
        }
        $user = $this->latestUser((int)$userId);
        if ($user === null) {
            return false;
        }

        $user->status = 'active';
        if (!empty($data['payment_method_id'])) {
            $user->payjp_payment_method_code = $data['payment_method_id'];
        }
        if (!empty($data['customer_id'])) {
            $user->payjp_customer_code = $data['customer_id'];
        }
        if (isset($data['card_brand'])) {
            $user->card_brand = $data['card_brand'];
        }
        if (isset($data['card_last4'])) {
            $user->card_last4 = $data['card_last4'];
        }
        if (isset($data['card_deadline'])) {
            $user->card_deadline = $data['card_deadline'];
        }
        $user->last_synced = new DateTime();
        if (!$this->payjpUsers->save($user)) {
            return false;
        }

        // 1ユーザー1有効カードの不変条件: 最新行の確定と同時に、同一ユーザーの他行
        // （カード変更前の active 行・古い仮登録行等）を deleted 化する。
        // 全 payjp_users 行を走査するオートチャージ処理での二重課金を防ぐ。
        $this->payjpUsers->updateAll(
            ['status' => 'deleted'],
            [
                'user_id' => (int)$userId,
                'id !=' => $user->id,
                'status !=' => 'deleted',
            ],
        );

        return true;
    }

    /**
     * 決済失敗イベントの確定。
     *
     * @param array<string, mixed> $data イベントデータ。
     * @param string $type イベント種別（ログ用）。
     * @return bool
     */
    private function failCharge(array $data, string $type): bool
    {
        $charge = $this->findChargeForWebhookData($data);
        if ($charge === null) {
            return false;
        }

        $charge->status = 'failure';
        $charge->payjp_status = $data['status'] ?? null;
        $charge->log = $data['failure_code'] ?? 'webhook failure: ' . $type;
        $this->payjpCharges->save($charge);

        return true;
    }

    /**
     * 失敗した課金レコードを記録する。
     *
     * @param \Payjp\Model\Entity\PayjpUser $user 対象顧客。
     * @param int $amount 課金額。
     * @param string $key 冪等性キー。
     * @param array<string, mixed>|null $result PaymentFlow 結果。
     * @param string $log 失敗内容。
     * @return void
     */
    private function recordFailureCharge(PayjpUser $user, int $amount, string $key, ?array $result, string $log): void
    {
        $charge = $this->payjpCharges->newEntity([
            'user_id' => $user->user_id,
            'point_book_id' => null,
            'status' => 'failure',
            'type' => 'auto_charge',
            'amount' => $amount,
            'payjp_status' => $result['status'] ?? null,
            'payjp_customer_code' => $user->payjp_customer_code,
            'payjp_payment_flow_code' => $result['id'] ?? null,
            'payjp_payment_method_code' => $user->payjp_payment_method_code,
            'idempotency_key' => $key,
            'log' => $log,
        ]);
        $this->payjpCharges->save($charge);
    }

    /**
     * off-session オートチャージ対象の顧客を取得する。
     *
     * status が active / suspended かつ payjp_payment_method_code を持つ顧客を引く。
     * chargeAuto() / chargeAutoIfBelow() で共用する。
     *
     * @param int $userId 対象ユーザーID。
     * @return \Payjp\Model\Entity\PayjpUser|null
     */
    private function findChargeableUser(int $userId): ?PayjpUser
    {
        return $this->payjpUsers->find()
            ->where([
                'PayjpUsers.user_id' => $userId,
                'PayjpUsers.status IN' => ['active', 'suspended'],
                'PayjpUsers.payjp_payment_method_code IS NOT' => null,
            ])
            ->first();
    }

    /**
     * ユーザーの最新の payjp_users レコードを取得する。
     *
     * @param int $userId 対象ユーザーID。
     * @return \Payjp\Model\Entity\PayjpUser|null
     */
    private function latestUser(int $userId): ?PayjpUser
    {
        return $this->payjpUsers->find()
            ->where(['PayjpUsers.user_id' => $userId])
            ->orderBy(['PayjpUsers.id' => 'DESC'])
            ->first();
    }

    /**
     * Member.Users から Checkout 用のメールアドレスを取得する。
     * 未登録・空文字の場合は null（customer_email を送らず PAY.JP 側入力に任せる）。
     *
     * @param int $userId 対象ユーザーID。
     * @return string|null
     */
    private function userEmail(int $userId): ?string
    {
        try {
            $user = TableRegistry::getTableLocator()->get('Member.Users')->get($userId);
        } catch (Throwable $e) {
            return null;
        }
        $email = trim((string)($user->email ?? ''));

        return $email !== '' ? $email : null;
    }
}
