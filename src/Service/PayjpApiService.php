<?php
declare(strict_types=1);

namespace Payjp\Service;

use Cake\Core\Configure;
use Cake\Log\Log;
use PAYJPV2\Api\CheckoutSessionsApi;
use PAYJPV2\Api\CustomersApi;
use PAYJPV2\Api\PaymentFlowsApi;
use PAYJPV2\Api\PaymentMethodsApi;
use PAYJPV2\ApiException;
use PAYJPV2\Configuration;
use PAYJPV2\Model\CheckoutSessionCreateRequest;
use PAYJPV2\Model\CheckoutSessionMode;
use PAYJPV2\Model\CustomerCreation;
use PAYJPV2\Model\Currency;
use PAYJPV2\Model\LineItemRequest;
use PAYJPV2\Model\PaymentFlowCreateRequest;
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
        $this->secretKey = $secretKey ?? (string)Configure::read('Payjp.secretKey');
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
     * @param array<string, mixed> $params mode(setup|payment) / amount / success_url / cancel_url / user_id 等。
     * @return array{id: string, url: string}|false
     */
    public function createCheckoutSession(array $params): array|false
    {
        try {
            $mode = CheckoutSessionMode::from((string)($params['mode'] ?? 'payment'));
            $request = new CheckoutSessionCreateRequest();
            $request->setMode($mode);
            if (!empty($params['success_url'])) {
                $request->setSuccessUrl((string)$params['success_url']);
            }
            if (!empty($params['cancel_url'])) {
                $request->setCancelUrl((string)$params['cancel_url']);
            }
            if (isset($params['user_id'])) {
                $request->setMetadata(['user_id' => (string)$params['user_id']]);
            }

            if ($mode === CheckoutSessionMode::SETUP) {
                // カード登録は顧客を必ず作成する
                $request->setCustomerCreation(CustomerCreation::ALWAYS);
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
     * Checkout Session を取得し、確定に必要な情報を返す。
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
            $paymentMethodId = method_exists($response, 'getPaymentMethodId') ? $response->getPaymentMethodId() : null;
            [$cardBrand, $cardLast4] = $this->cardDetails($paymentMethodId);

            return [
                'id' => $response->getId(),
                'mode' => $response->getMode()->value,
                'status' => $response->getStatus()->value,
                'payment_flow_id' => $response->getPaymentFlowId(),
                'setup_flow_id' => $response->getSetupFlowId(),
                'customer_id' => $response->getCustomerId(),
                'payment_method_id' => $paymentMethodId,
                'card_brand' => $cardBrand,
                'card_last4' => $cardLast4,
                'user_id' => $metadata['user_id'] ?? null,
            ];
        } catch (Throwable $e) {
            Log::error('PayjpApiService::getCheckoutSession failed: ' . $e->getMessage());

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
            [$cardBrand, $cardLast4] = $this->cardDetails($paymentMethodId);

            return [
                'id' => $response->getId(),
                'status' => $response->getStatus()->value,
                'payment_method_id' => $paymentMethodId,
                'card_brand' => $cardBrand,
                'card_last4' => $cardLast4,
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
     * PaymentMethod のカードブランド・下4桁をベストエフォートで取得する。
     *
     * @param string|null $paymentMethodId PaymentMethod ID。
     * @return array{0: ?string, 1: ?string}
     */
    private function cardDetails(?string $paymentMethodId): array
    {
        if (empty($paymentMethodId)) {
            return [null, null];
        }
        try {
            $api = new PaymentMethodsApi(null, $this->config());
            $card = $api->getPaymentMethod($paymentMethodId)->getCard();

            return [$card->getBrand(), $card->getLast4()];
        } catch (Throwable $e) {
            Log::warning('PayjpApiService::cardDetails failed: ' . $e->getMessage());

            return [null, null];
        }
    }
}
