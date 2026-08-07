<?php
declare(strict_types=1);

namespace Payjp\Service;

use Cake\Core\Configure;
use Cake\I18n\Date;
use Cake\Log\Log;
use PAYJPV2\Api\CheckoutSessionsApi;
use PAYJPV2\Api\CustomersApi;
use PAYJPV2\Api\EventsApi;
use PAYJPV2\Api\PaymentFlowsApi;
use PAYJPV2\Api\PaymentMethodsApi;
use PAYJPV2\Api\SetupFlowsApi;
use PAYJPV2\ApiException;
use PAYJPV2\Configuration;
use PAYJPV2\Model\CheckoutSessionCreateRequest;
use PAYJPV2\Model\CheckoutSessionMode;
use PAYJPV2\Model\Currency;
use PAYJPV2\Model\CustomerCreateRequest;
use PAYJPV2\Model\CustomerCreation;
use PAYJPV2\Model\LineItemRequest;
use PAYJPV2\Model\PaymentFlowCreateRequest;
use PAYJPV2\Model\PaymentFlowDataRequest;
use PAYJPV2\Model\PaymentMethodTypes;
use PAYJPV2\Model\PriceDataRequest;
use PAYJPV2\Model\ProductDataRequest;
use Throwable;

/**
 * PAY.JP API v2 SDK（payjp/payjpv2-php）の薄いラッパー。
 *
 * 公開メソッドは SDK 型を漏らさず array|false / bool を返す。PayjpService からはこのクラスを
 * 注入して利用し、テストでは createMock(PayjpApiService) で差し替える。SDK 呼び出しは
 * 各メソッド本体内に閉じており、通信・処理の失敗は Log に記録して失敗値を返す。
 */
class PayjpApiService
{
    private string $secretKey;

    /**
     * @param string|null $secretKey PAY.JP シークレットキー。未指定時は設定値を使用。
     */
    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? (string)Configure::read('Payjp.secret');
    }

    /**
     * 認証済み Configuration を生成する。
     */
    private function config(): Configuration
    {
        return Configuration::getDefaultConfiguration()->setAccessToken($this->secretKey);
    }

    /**
     * Checkout Session を作成し、リダイレクト URL とセッション ID を返す。
     *
     * @param array<string, mixed> $params mode(setup|payment) / amount / success_url / cancel_url / customer_id / customer_email / user_id / idempotency_key / payment_method_types 等。
     * @return array{id: string, url: string}|false
     */
    public function createCheckoutSession(array $params): array|false
    {
        try {
            $mode = CheckoutSessionMode::from((string)($params['mode'] ?? 'payment'));
            $request = new CheckoutSessionCreateRequest();
            $request->setMode($mode);
            if (!empty($params['payment_method_types']) && is_array($params['payment_method_types'])) {
                $types = [];
                foreach ($params['payment_method_types'] as $type) {
                    $types[] = PaymentMethodTypes::from((string)$type);
                }
                $request->setPaymentMethodTypes($types);
            }
            if (!empty($params['success_url'])) {
                $request->setSuccessUrl((string)$params['success_url']);
            }
            if (!empty($params['cancel_url'])) {
                $request->setCancelUrl((string)$params['cancel_url']);
            }
            if (!empty($params['customer_id'])) {
                $request->setCustomerId((string)$params['customer_id']);
            }
            if (!empty($params['customer_email'])) {
                $request->setCustomerEmail((string)$params['customer_email']);
            }
            if (isset($params['user_id'])) {
                $request->setMetadata(['user_id' => (string)$params['user_id']]);
            }

            if ($mode === CheckoutSessionMode::SETUP) {
                // 既存顧客を紐付ける場合は CustomerCreation 不要。未指定時のみ自動作成。
                if (empty($params['customer_id'])) {
                    $request->setCustomerCreation(CustomerCreation::ALWAYS);
                }
            } else {
                // 都度課金は金額を line_items で指定する
                $product = (new ProductDataRequest())->setName($params['product_name'] ?? 'ポイントチャージ');
                $priceData = (new PriceDataRequest())
                    ->setCurrency(Currency::JPY)
                    ->setUnitAmount((int)($params['amount'] ?? 0))
                    ->setProductData($product);
                $lineItem = (new LineItemRequest())
                    ->setPriceData($priceData)
                    ->setQuantity(1);
                $request->setLineItems([$lineItem]);
                $request->setCurrency(Currency::JPY);

                // payment_flow.succeeded webhook で pending charge を引けるよう相関キーを Flow に引き継ぐ
                $flowMeta = [];
                if (!empty($params['idempotency_key'])) {
                    $flowMeta['idempotency_key'] = (string)$params['idempotency_key'];
                }
                if (isset($params['user_id'])) {
                    $flowMeta['user_id'] = (string)$params['user_id'];
                }
                if ($flowMeta !== []) {
                    $request->setPaymentFlowData((new PaymentFlowDataRequest())->setMetadata($flowMeta));
                }
            }

            $api = new CheckoutSessionsApi(null, $this->config());
            $idempotencyKey = !empty($params['idempotency_key']) ? (string)$params['idempotency_key'] : null;
            $response = $api->createCheckoutSession($request, $idempotencyKey);

            return [
                'id' => $response->getId(),
                'url' => $response->getUrl(),
            ];
        } catch (Throwable $e) {
            Log::error('PayjpApiService::createCheckoutSession failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * 顧客を新規作成し、自動採番された顧客 ID（cus_...）を返す。
     *
     * @param string|null $email メールアドレス。
     * @param array<string, mixed> $metadata メタデータ（例: user_id）。
     * @return string|false
     */
    public function createCustomer(?string $email = null, array $metadata = []): string|false
    {
        try {
            $request = new CustomerCreateRequest();
            if ($email !== null && $email !== '') {
                $request->setEmail($email);
            }
            if ($metadata !== []) {
                $request->setMetadata($metadata);
            }
            $created = (new CustomersApi(null, $this->config()))->createCustomer($request);

            return $created->getId();
        } catch (Throwable $e) {
            Log::error('PayjpApiService::createCustomer failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Checkout Session を取得し、確定に必要な情報を返す。
     *
     * Checkout Session 自体に payment_method_id はないため、mode に応じて
     * SetupFlow / PaymentFlow から pm_ を取得し、カード詳細を付与する。
     *
     * @param string $sessionId Checkout Session ID（cs_...）。
     * @return array<string, mixed>|false
     */
    public function getCheckoutSession(string $sessionId): array|false
    {
        try {
            $api = new CheckoutSessionsApi(null, $this->config());
            $response = $api->getCheckoutSession($sessionId);
            $metadata = $response->getMetadata();
            $mode = $response->getMode()->value;
            $setupFlowId = $response->getSetupFlowId();
            $paymentFlowId = $response->getPaymentFlowId();
            $paymentMethodId = $this->paymentMethodIdFromFlows($mode, $setupFlowId, $paymentFlowId);
            [$cardBrand, $cardLast4, $cardDeadline] = $this->cardDetails($paymentMethodId);

            return [
                'id' => $response->getId(),
                'mode' => $mode,
                'status' => $response->getStatus()->value,
                'payment_flow_id' => $paymentFlowId,
                'setup_flow_id' => $setupFlowId,
                'customer_id' => $response->getCustomerId(),
                'payment_method_id' => $paymentMethodId,
                'card_brand' => $cardBrand,
                'card_last4' => $cardLast4,
                'card_deadline' => $cardDeadline,
                'user_id' => $metadata['user_id'] ?? null,
            ];
        } catch (Throwable $e) {
            Log::error('PayjpApiService::getCheckoutSession failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * mode に応じて SetupFlow / PaymentFlow から PaymentMethod ID を取得する。
     *
     * Flow 取得失敗時はログ警告し null を返す（Checkout Session 本体の確定は妨げない）。
     *
     * @param string $mode setup|payment。
     * @param string|null $setupFlowId SetupFlow ID（setup モード時）。
     * @param string|null $paymentFlowId PaymentFlow ID（payment モード時）。
     * @return string|null
     */
    private function paymentMethodIdFromFlows(string $mode, ?string $setupFlowId, ?string $paymentFlowId): ?string
    {
        try {
            if ($mode === 'setup' && !empty($setupFlowId)) {
                $flow = (new SetupFlowsApi(null, $this->config()))->getSetupFlow($setupFlowId);

                return $flow->getPaymentMethodId();
            }
            if ($mode === 'payment' && !empty($paymentFlowId)) {
                $flow = (new PaymentFlowsApi(null, $this->config()))->getPaymentFlow($paymentFlowId);

                return $flow->getPaymentMethodId();
            }
        } catch (Throwable $e) {
            Log::warning('PayjpApiService::paymentMethodIdFromFlows failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Webhook イベントを PAY.JP から再取得し、handleWebhook が読める形に正規化する。
     *
     * 生 webhook ペイロードは event id だけを信用し、ここで正本（authoritative event）を再取得する
     * ことを正当性検証の代替とする。Checkout Session 系イベントは object id（cs_...）から
     * getCheckoutSession() を再取得して正規化を流用し、それ以外（PaymentFlow 系）は event の
     * object をベストエフォートで最小正規化する。
     *
     * @param string $eventId PAY.JP イベント ID。
     * @return array{type: string, data: array<string, mixed>}|false
     */
    public function getEvent(string $eventId): array|false
    {
        try {
            $api = new EventsApi(null, $this->config());
            $response = $api->getEvent($eventId);
            $type = $response->getType();
            $object = $response->getData();
            $objectId = (string)($object['id'] ?? '');

            // Checkout Session 系は正本を再取得し getCheckoutSession の正規化を流用する。
            if (str_starts_with($objectId, 'cs_')) {
                $data = $this->getCheckoutSession($objectId);
                if ($data === false) {
                    return false;
                }
            } else {
                // PaymentFlow 等は event の object をそのまま最小正規化する。
                // PaymentFlow には checkout_session_id が無いため、metadata.idempotency_key 等で自社 charge を引く。
                $metadata = (array)($object['metadata'] ?? []);
                $data = [
                    'id' => $objectId,
                    'status' => $object['status'] ?? null,
                    'payment_flow_id' => $object['id'] ?? null,
                    'customer_id' => $object['customer_id'] ?? ($object['customer'] ?? null),
                    'payment_method_id' => $object['payment_method_id'] ?? null,
                    'card_brand' => $object['card_brand'] ?? null,
                    'card_last4' => $object['card_last4'] ?? null,
                    'card_deadline' => $object['card_deadline'] ?? null,
                    'failure_code' => $object['failure_code'] ?? null,
                    'user_id' => $metadata['user_id'] ?? null,
                    'metadata' => $metadata,
                    'idempotency_key' => $metadata['idempotency_key'] ?? null,
                ];
                $checkoutSessionId = $object['checkout_session_id'] ?? ($metadata['checkout_session_id'] ?? null);
                if (is_string($checkoutSessionId) && str_starts_with($checkoutSessionId, 'cs_')) {
                    $data['checkout_session_id'] = $checkoutSessionId;
                }
            }

            return ['type' => $type, 'data' => $data];
        } catch (Throwable $e) {
            Log::error('PayjpApiService::getEvent failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * off-session の PaymentFlow を作成・即時確定し、決済結果を返す。
     *
     * @param int $amount 課金額（円）。
     * @param string $customerId PAY.JP 顧客 ID（cus_...）。
     * @param string $paymentMethodId PaymentMethod ID（pm_...）。
     * @param string $idempotencyKey 冪等性キー。
     * @return array<string, mixed>|false
     */
    public function createPaymentFlow(int $amount, string $customerId, string $paymentMethodId, string $idempotencyKey): array|false
    {
        try {
            $request = new PaymentFlowCreateRequest();
            $request->setAmount($amount);
            $request->setCurrency(Currency::JPY);
            $request->setCustomerId($customerId);
            $request->setPaymentMethodId($paymentMethodId);
            $request->setConfirm(true);

            $api = new PaymentFlowsApi(null, $this->config());
            $response = $api->createPaymentFlow($request, $idempotencyKey);

            $paymentMethodId = $response->getPaymentMethodId();
            [$cardBrand, $cardLast4, $cardDeadline] = $this->cardDetails($paymentMethodId);

            return [
                'id' => $response->getId(),
                'status' => $response->getStatus()->value,
                'payment_method_id' => $paymentMethodId,
                'card_brand' => $cardBrand,
                'card_last4' => $cardLast4,
                'card_deadline' => $cardDeadline,
            ];
        } catch (ApiException $e) {
            // カード拒否・パラメータ不正（4xx）は「決済失敗」として扱い、ステータス遷移に委ねる。
            // 通信・サーバ例外（5xx 等）は再スローし、呼び出し側で failure 扱いとする。
            $code = $e->getCode();
            if ($code >= 400 && $code < 500) {
                Log::warning('PayjpApiService::createPaymentFlow declined: ' . $e->getMessage());

                return [
                    'id' => null,
                    'status' => 'failed',
                    'payment_method_id' => null,
                    'card_brand' => null,
                    'card_last4' => null,
                    'card_deadline' => null,
                ];
            }
            Log::error('PayjpApiService::createPaymentFlow failed: ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * 顧客を削除する。
     *
     * @param string $customerId PAY.JP 顧客 ID（cus_...）。
     * @return bool
     */
    public function deleteCustomer(string $customerId): bool
    {
        $api = new CustomersApi(null, $this->config());
        $api->deleteCustomer($customerId);

        return true;
    }

    /**
     * PaymentMethod のカード有効期限（有効期限月の末日）をベストエフォートで取得する。
     *
     * card_deadline 未保存の既存行のバックフィル用公開ラッパー。
     *
     * @param string $paymentMethodId PaymentMethod ID。
     * @return \Cake\I18n\Date|null
     */
    public function cardDeadline(string $paymentMethodId): ?Date
    {
        [, , $cardDeadline] = $this->cardDetails($paymentMethodId);

        return $cardDeadline;
    }

    /**
     * PaymentMethod のカードブランド・下4桁・有効期限をベストエフォートで取得する。
     *
     * 有効期限は有効期限月の末日（Date）。exp_month / exp_year が未設定の場合は
     * 有効期限のみ null とし、ブランド・下4桁は返す。
     *
     * @param string|null $paymentMethodId PaymentMethod ID。
     * @return array{0: ?string, 1: ?string, 2: ?\Cake\I18n\Date}
     */
    private function cardDetails(?string $paymentMethodId): array
    {
        if (empty($paymentMethodId)) {
            return [null, null, null];
        }
        try {
            $api = new PaymentMethodsApi(null, $this->config());
            $card = $api->getPaymentMethod($paymentMethodId)->getCard();

            $cardDeadline = null;
            try {
                $cardDeadline = Date::create($card->getExpYear(), $card->getExpMonth(), 1)->lastOfMonth();
            } catch (Throwable $e) {
                // exp_month / exp_year 未設定（SDK は LogicException を投げる）は有効期限のみ null とする。
                Log::warning('PayjpApiService::cardDetails exp unavailable: ' . $e->getMessage());
            }

            return [$card->getBrand(), $card->getLast4(), $cardDeadline];
        } catch (Throwable $e) {
            Log::warning('PayjpApiService::cardDetails failed: ' . $e->getMessage());

            return [null, null, null];
        }
    }
}
