<?php
declare(strict_types=1);

namespace Payjp\Service;

use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Payjp\Model\Entity\PayjpCharge;
use Payjp\Model\Entity\PayjpUser;
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
    private $payjpUsers;

    /**
     * @var \Payjp\Model\Table\PayjpChargesTable
     */
    private $payjpCharges;

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
     * @param array<string, mixed> $options success_url / cancel_url 等。
     * @return string|false リダイレクト URL、失敗時 false。
     */
    public function createSetupCheckout(int $userId, int $autoChargeAmount, array $options = []): string|false
    {
        try {
            $result = $this->api->createCheckoutSession([
                'mode' => 'setup',
                'user_id' => $userId,
                'success_url' => $options['success_url'] ?? null,
                'cancel_url' => $options['cancel_url'] ?? null,
                'idempotency_key' => $this->generateIdempotencyKey(),
            ]);
            if ($result === false) {
                return false;
            }

            // 確定前の仮登録（status は active ではない / PaymentMethod 未保存）
            $user = $this->payjpUsers->newEntity([
                'user_id' => $userId,
                'status' => 'inactive',
                'type' => 'auto_charge',
                'auto_charge_amount' => $autoChargeAmount,
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
     * 都度課金。mode=payment の Checkout Session を作成し payjp_charges を pending で記録する。
     *
     * @param int $userId 対象ユーザーID。
     * @param int $amount 課金額（円）。
     * @param array<string, mixed> $options success_url / cancel_url 等。
     * @return string|false リダイレクト URL、失敗時 false。
     */
    public function createPaymentCheckout(int $userId, int $amount, array $options = []): string|false
    {
        try {
            $key = $this->generateIdempotencyKey();
            $result = $this->api->createCheckoutSession([
                'mode' => 'payment',
                'amount' => $amount,
                'user_id' => $userId,
                'success_url' => $options['success_url'] ?? null,
                'cancel_url' => $options['cancel_url'] ?? null,
                'idempotency_key' => $key,
            ]);
            if ($result === false) {
                return false;
            }

            $charge = $this->payjpCharges->newEntity([
                'user_id' => $userId,
                'point_book_id' => null,
                'status' => 'pending',
                'type' => 'one_time',
                'amount' => $amount,
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
        $user = $this->payjpUsers->find()
            ->where([
                'PayjpUsers.user_id' => $userId,
                'PayjpUsers.status IN' => ['active', 'suspended'],
                'PayjpUsers.payjp_payment_method_code IS NOT' => null,
            ])
            ->first();
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
                $key
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
                'payjp_status' => $result['status'],
                'payjp_customer_code' => $user->payjp_customer_code,
                'payjp_payment_flow_code' => $result['id'] ?? null,
                'payjp_payment_method_code' => $result['payment_method_id'] ?? $user->payjp_payment_method_code,
                'card_brand' => $result['card_brand'] ?? $user->card_brand,
                'card_last4' => $result['card_last4'] ?? $user->card_last4,
                'idempotency_key' => $key,
            ]);
            $this->payjpCharges->save($charge);

            $pointBook = (new PointService())->charge($user->user_id, $amount, [
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
     * ユーザー退会処理。顧客を削除し payjp_users を deleted にする。
     *
     * @param int $userId 対象ユーザーID。
     * @return \Payjp\Model\Entity\PayjpUser|false 成功時 PayjpUser、失敗時 false。
     */
    public function deleteCustomer(int $userId): PayjpUser|false
    {
        $user = $this->payjpUsers->find()->where(['PayjpUsers.user_id' => $userId])->first();
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
        if ($type === 'checkout_session.completed' && $mode === 'setup') {
            return $this->confirmSetup($data);
        }

        // 都度課金（one_time）成功
        if ($type === 'payment_flow.succeeded' || ($type === 'checkout_session.completed' && $mode === 'payment')) {
            return $this->confirmCharge($data);
        }

        // 失敗系
        if (str_contains($type, 'failed') || str_contains($type, 'canceled')) {
            return $this->failCharge($data, $type);
        }

        return false;
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
     * 都度課金の成功確定（webhook 経路）。
     *
     * @param array<string, mixed> $data イベントデータ。
     * @return bool
     */
    private function confirmCharge(array $data): bool
    {
        $sessionId = $data['id'] ?? null;
        if (empty($sessionId)) {
            return false;
        }
        $charge = $this->payjpCharges->find('byCheckoutSession', sessionId: (string)$sessionId)->first();
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

        $pointBook = (new PointService())->charge((int)$charge->user_id, (int)$charge->amount, [
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
        $user->last_synced = new DateTime();

        return (bool)$this->payjpUsers->save($user);
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
        $sessionId = $data['id'] ?? null;
        if (empty($sessionId)) {
            return false;
        }
        $charge = $this->payjpCharges->find('byCheckoutSession', sessionId: (string)$sessionId)->first();
        if ($charge === null) {
            return false;
        }

        $charge->status = 'failure';
        $charge->payjp_status = $data['status'] ?? null;
        $charge->log = $data['failure_code'] ?? ('webhook failure: ' . $type);
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
}
