<?php

declare(strict_types=1);

namespace Payjp\Test\TestCase\Service;

use Cake\TestSuite\TestCase;
use Cake\Core\Configure;
use Payjp\Model\Entity\PayjpCharge;
use Payjp\Model\Entity\PayjpUser;
use Payjp\Service\PayjpApiService;
use Payjp\Service\PayjpService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use RuntimeException;

/**
 * Payjp\Service\PayjpService Test Case
 *
 * PAY.JP API は薄いラッパー Payjp\Service\PayjpApiService をモックして差し替える
 * （Invoice の MfApiService と同じ方式）。PointService::charge() はテスト DB に対して
 * 実際に走らせ、point_books / point_users への反映と point_book_id 保存を検証する。
 *
 * NOTE: PayjpApiService のメソッド・戻り値、および webhook イベント / checkout session の
 * 配列形状は本テストが前提とする「契約」であり、実装時に api-payjp / cakephp-implement Skill
 * に整合させる。形状はいずれも代表値。
 *
 * @uses \Payjp\Service\PayjpService
 */
#[AllowMockObjectsWithoutExpectations]
class PayjpServiceTest extends TestCase
{
    protected array $fixtures = [
        'plugin.Payjp.PayjpUsers',
        'plugin.Payjp.PayjpCharges',
        'plugin.Payjp.Users',
        'plugin.Payjp.Companies',
        'plugin.Payjp.PointBooks',
        'plugin.Payjp.PointUsers',
    ];

    /**
     * 全メソッドを成功扱いにした PayjpApiService モック。
     */
    private function apiSuccess(array $overrides = []): PayjpApiService
    {
        $mock = $this->createMock(PayjpApiService::class);
        $mock->method('createCheckoutSession')->willReturn(
            $overrides['createCheckoutSession'] ?? ['id' => 'cs_new_001', 'url' => 'https://checkout.pay.jp/cs_new_001'],
        );
        $mock->method('getCheckoutSession')->willReturn(
            $overrides['getCheckoutSession'] ?? [
                'id' => 'cs_new_001',
                'mode' => 'payment',
                'status' => 'completed',
                'payment_flow_id' => 'pf_new_001',
                'customer_id' => 'cus_new_001',
                'payment_method_id' => 'pm_new_001',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'user_id' => 1,
            ],
        );
        $mock->method('createPaymentFlow')->willReturn(
            $overrides['createPaymentFlow'] ?? [
                'id' => 'pf_new_001',
                'status' => 'succeeded',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
            ],
        );
        $mock->method('deleteCustomer')->willReturn($overrides['deleteCustomer'] ?? true);

        return $mock;
    }

    private function payjpUsers()
    {
        return $this->getTableLocator()->get('Payjp.PayjpUsers');
    }

    private function payjpCharges()
    {
        return $this->getTableLocator()->get('Payjp.PayjpCharges');
    }

    private function pointUsers()
    {
        return $this->getTableLocator()->get('Point.PointUsers');
    }

    //composer test:payjp -- tests/TestCase/Service/PayjpServiceTest.php --filter testCreatePaymentCheckout1
    public function testCreatePaymentCheckout1(): void
    {
        Configure::write('Payjp.secret', Configure::read('Payjp.secret_test'));
        $siteUrl = Configure::read('DUB_APP.site_url');
        $service = new PayjpService();

        $url = $service->createPaymentCheckout(5, 3000, [
            'success_url' => $siteUrl . '/payjp/payment/complete',
            'cancel_url' => $siteUrl . '/payjp/payment/complete',
        ]);
        $id = str_replace('https://c.pay.jp/c/pay/', '', $url);
        $this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);

        $charge = $this->payjpCharges()->find()->where(['payjp_checkout_session_code' => $id])->first();
        $this->assertNotNull($charge);
        $this->assertSame('pending', $charge->status);
        $this->assertSame('one_time', $charge->type);
        $this->assertSame(3000, $charge->amount);
        $this->assertNull($charge->point_book_id, '確定前は point_book_id NULL');
    }

    // ============================================================
    // createSetupCheckout
    // ============================================================

    public function testCreateSetupCheckout_success_returnsUrlAndProvisionsUser(): void
    {
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $url = $service->createSetupCheckout(5, 12000, [
            'success_url' => 'https://example.com/payjp/setup/success',
            'cancel_url' => 'https://example.com/payjp/setup/cancel',
        ]);

        $this->assertSame('https://checkout.pay.jp/cs_new_001', $url);

        $user = $this->payjpUsers()->find()->where(['user_id' => 5])->first();
        $this->assertNotNull($user, '仮登録の payjp_users が作成される');
        $this->assertSame('auto_charge', $user->type);
        $this->assertSame(12000, $user->auto_charge_amount);
        // 確定前：PaymentMethod 未保存・active ではない
        $this->assertEmpty($user->payjp_payment_method_code);
        $this->assertNotSame('active', $user->status);
    }

    public function testCreateSetupCheckout_apiReturnsFalse_returnsFalse(): void
    {
        $api = $this->apiSuccess(['createCheckoutSession' => false]);
        $service = new PayjpService($api);

        $result = $service->createSetupCheckout(5, 12000, []);
        $this->assertFalse($result);
    }

    public function testCreateSetupCheckout_apiThrows_returnsFalse(): void
    {
        $api = $this->createMock(PayjpApiService::class);
        $api->method('createCheckoutSession')->willThrowException(new RuntimeException('api error'));
        $service = new PayjpService($api);

        $result = $service->createSetupCheckout(5, 12000, []);
        $this->assertFalse($result);
    }

    // ============================================================
    // createPaymentCheckout
    // ============================================================

    public function testCreatePaymentCheckout_success_returnsUrlAndRecordsPending(): void
    {
        $api = $this->apiSuccess(['createCheckoutSession' => ['id' => 'cs_pay_777', 'url' => 'https://checkout.pay.jp/cs_pay_777']]);
        $service = new PayjpService($api);

        $url = $service->createPaymentCheckout(5, 3000, [
            'success_url' => 'https://example.com/payjp/charge/success',
            'cancel_url' => 'https://example.com/payjp/charge/cancel',
        ]);

        $this->assertSame('https://checkout.pay.jp/cs_pay_777', $url);

        $charge = $this->payjpCharges()->find()->where(['payjp_checkout_session_code' => 'cs_pay_777'])->first();
        $this->assertNotNull($charge);
        $this->assertSame('pending', $charge->status);
        $this->assertSame('one_time', $charge->type);
        $this->assertSame(3000, $charge->amount);
        $this->assertNull($charge->point_book_id, '確定前は point_book_id NULL');
    }

    public function testCreatePaymentCheckout_apiReturnsFalse_returnsFalse(): void
    {
        $api = $this->apiSuccess(['createCheckoutSession' => false]);
        $service = new PayjpService($api);

        $result = $service->createPaymentCheckout(5, 3000, []);
        $this->assertFalse($result);
    }

    public function testCreatePaymentCheckout_apiThrows_returnsFalse(): void
    {
        $api = $this->createMock(PayjpApiService::class);
        $api->method('createCheckoutSession')->willThrowException(new RuntimeException('api error'));
        $service = new PayjpService($api);

        $result = $service->createPaymentCheckout(5, 3000, []);
        $this->assertFalse($result);
    }

    // ============================================================
    // chargeAuto（off-session 課金・ステータス遷移）
    // ============================================================

    public function testChargeAuto_activeSuccess_staysActiveAndChargesPoint(): void
    {
        $api = $this->apiSuccess([
            'createPaymentFlow' => ['id' => 'pf_auto_1', 'status' => 'succeeded', 'card_brand' => 'Visa', 'card_last4' => '4242'],
        ]);
        $service = new PayjpService($api);

        $before = $this->pointUsers()->find()->where(['user_id' => 1])->first()->point;

        $charge = $service->chargeAuto(1);

        $this->assertInstanceOf(PayjpCharge::class, $charge);
        $this->assertSame('success', $charge->status);
        $this->assertSame('auto_charge', $charge->type);
        $this->assertSame(10000, $charge->amount); // payjp_users.auto_charge_amount
        $this->assertSame('pf_auto_1', $charge->payjp_payment_flow_code);
        $this->assertNotNull($charge->point_book_id, 'PointService::charge() の point_book_id を保存');
        $this->assertNotEmpty($charge->idempotency_key);

        // payjp_users は active 維持・last_synced 更新
        $user = $this->payjpUsers()->get(1);
        $this->assertSame('active', $user->status);
        $this->assertNotNull($user->last_synced);

        // ポイント加算
        $after = $this->pointUsers()->find()->where(['user_id' => 1])->first()->point;
        $this->assertSame($before + 10000, $after);
    }

    public function testChargeAuto_suspendedSuccess_recoversToActive(): void
    {
        // user 3: suspended + pm あり
        $api = $this->apiSuccess([
            'createPaymentFlow' => ['id' => 'pf_auto_3', 'status' => 'succeeded', 'card_brand' => 'Mastercard', 'card_last4' => '5555'],
        ]);
        $service = new PayjpService($api);

        $charge = $service->chargeAuto(3);

        $this->assertInstanceOf(PayjpCharge::class, $charge);
        $this->assertSame('success', $charge->status);
        $this->assertSame('active', $this->payjpUsers()->get(3)->status);
    }

    public function testChargeAuto_activeFailure_movesToSuspended(): void
    {
        $api = $this->apiSuccess([
            'createPaymentFlow' => ['id' => 'pf_auto_1', 'status' => 'canceled'],
        ]);
        $service = new PayjpService($api);

        $result = $service->chargeAuto(1);

        $this->assertFalse($result);
        $this->assertSame('suspended', $this->payjpUsers()->get(1)->status);

        $charge = $this->payjpCharges()->find()
            ->where(['user_id' => 1, 'status' => 'failure'])
            ->orderBy(['id' => 'DESC'])->first();
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->log);
        $this->assertNull($charge->point_book_id);
    }

    public function testChargeAuto_suspendedFailure_movesToInactive(): void
    {
        $api = $this->apiSuccess([
            'createPaymentFlow' => ['id' => 'pf_auto_3', 'status' => 'failed'],
        ]);
        $service = new PayjpService($api);

        $result = $service->chargeAuto(3);

        $this->assertFalse($result);
        $this->assertSame('inactive', $this->payjpUsers()->get(3)->status);
    }

    public function testChargeAuto_requiresAction_treatedAsFailure(): void
    {
        // 3D セキュア等で requires_action は確定不能 → 失敗扱い
        $api = $this->apiSuccess([
            'createPaymentFlow' => ['id' => 'pf_auto_1', 'status' => 'requires_action'],
        ]);
        $service = new PayjpService($api);

        $result = $service->chargeAuto(1);

        $this->assertFalse($result);
        $this->assertSame('suspended', $this->payjpUsers()->get(1)->status);
    }

    public function testChargeAuto_activeException_movesToFailure(): void
    {
        $api = $this->createMock(PayjpApiService::class);
        $api->method('createPaymentFlow')->willThrowException(new RuntimeException('network error'));
        $service = new PayjpService($api);

        $result = $service->chargeAuto(1);

        $this->assertFalse($result);
        $user = $this->payjpUsers()->get(1);
        $this->assertSame('failure', $user->status);
        $this->assertNotEmpty($user->log);
    }

    public function testChargeAuto_suspendedException_movesToFailure(): void
    {
        $api = $this->createMock(PayjpApiService::class);
        $api->method('createPaymentFlow')->willThrowException(new RuntimeException('network error'));
        $service = new PayjpService($api);

        $result = $service->chargeAuto(3);

        $this->assertFalse($result);
        $this->assertSame('failure', $this->payjpUsers()->get(3)->status);
    }

    public function testChargeAuto_noActiveCustomerWithPaymentMethod_returnsFalse(): void
    {
        // user 2: active だが payjp_payment_method_code が NULL → 対象外
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $this->assertFalse($service->chargeAuto(2));
    }

    public function testChargeAuto_noPayjpUser_returnsFalse(): void
    {
        // user 5: payjp_users 行なし
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $this->assertFalse($service->chargeAuto(5));
    }

    // ============================================================
    // chargeAutoIfBelow（残高 < auto_charge_amount のときのみ課金）
    // ============================================================

    public function testChargeAutoIfBelow_activeBalanceBelow_charges(): void
    {
        // user 1: auto_charge_amount=10000 / 残高 point=1000 < 10000 → 課金
        $api = $this->apiSuccess([
            'createPaymentFlow' => ['id' => 'pf_below_1', 'status' => 'succeeded', 'card_brand' => 'Visa', 'card_last4' => '4242'],
        ]);
        $service = new PayjpService($api);

        $before = $this->pointUsers()->find()->where(['user_id' => 1])->first()->point;

        $charge = $service->chargeAutoIfBelow(1);

        $this->assertInstanceOf(PayjpCharge::class, $charge);
        $this->assertSame('success', $charge->status);
        $this->assertSame('auto_charge', $charge->type);
        $this->assertSame(10000, $charge->amount);

        $after = $this->pointUsers()->find()->where(['user_id' => 1])->first()->point;
        $this->assertSame($before + 10000, $after);
    }

    public function testChargeAutoIfBelow_suspendedBalanceBelow_chargesAndRecovers(): void
    {
        // user 3: suspended / auto_charge_amount=8000 / 残高 point=500 < 8000 → 課金して active 復帰
        $api = $this->apiSuccess([
            'createPaymentFlow' => ['id' => 'pf_below_3', 'status' => 'succeeded', 'card_brand' => 'Mastercard', 'card_last4' => '5555'],
        ]);
        $service = new PayjpService($api);

        $charge = $service->chargeAutoIfBelow(3);

        $this->assertInstanceOf(PayjpCharge::class, $charge);
        $this->assertSame('success', $charge->status);
        $this->assertSame('active', $this->payjpUsers()->get(3)->status);
    }

    public function testChargeAutoIfBelow_balanceAtOrAboveAmount_doesNotCharge(): void
    {
        // 残高 == auto_charge_amount（10000）→ 下回っていないので課金しない
        $pointUsers = $this->pointUsers();
        $user = $pointUsers->find()->where(['user_id' => 1])->first();
        $user->point = 10000;
        $pointUsers->save($user);

        $api = $this->createMock(PayjpApiService::class);
        $api->expects($this->never())->method('createPaymentFlow');
        $service = new PayjpService($api);

        $this->assertFalse($service->chargeAutoIfBelow(1));

        // 残高据え置き・payjp_users 据え置き
        $this->assertSame(10000, $this->pointUsers()->find()->where(['user_id' => 1])->first()->point);
        $this->assertSame('active', $this->payjpUsers()->get(1)->status);
    }

    public function testChargeAutoIfBelow_noChargeableCustomer_returnsFalse(): void
    {
        // user 2: active だが payjp_payment_method_code が NULL → 対象外
        $api = $this->createMock(PayjpApiService::class);
        $api->expects($this->never())->method('createPaymentFlow');
        $service = new PayjpService($api);

        $this->assertFalse($service->chargeAutoIfBelow(2));
    }

    public function testChargeAutoIfBelow_noPayjpUser_returnsFalse(): void
    {
        // user 5: payjp_users 行なし
        $api = $this->createMock(PayjpApiService::class);
        $api->expects($this->never())->method('createPaymentFlow');
        $service = new PayjpService($api);

        $this->assertFalse($service->chargeAutoIfBelow(5));
    }

    // ============================================================
    // deleteCustomer
    // ============================================================

    public function testDeleteCustomer_success_setsStatusDeleted(): void
    {
        $api = $this->apiSuccess(['deleteCustomer' => true]);
        $service = new PayjpService($api);

        $result = $service->deleteCustomer(1);

        $this->assertInstanceOf(PayjpUser::class, $result);
        $this->assertSame('deleted', $result->status);
        $this->assertSame('deleted', $this->payjpUsers()->get(1)->status);
    }

    public function testDeleteCustomer_apiReturnsFalse_returnsFalse(): void
    {
        $api = $this->apiSuccess(['deleteCustomer' => false]);
        $service = new PayjpService($api);

        $result = $service->deleteCustomer(1);

        $this->assertFalse($result);
        $this->assertNotSame('deleted', $this->payjpUsers()->get(1)->status);
    }

    public function testDeleteCustomer_apiThrows_returnsFalse(): void
    {
        $api = $this->createMock(PayjpApiService::class);
        $api->method('deleteCustomer')->willThrowException(new RuntimeException('api error'));
        $service = new PayjpService($api);

        $result = $service->deleteCustomer(1);

        $this->assertFalse($result);
        $user = $this->payjpUsers()->get(1);
        $this->assertSame('failure', $user->status);
        $this->assertNotEmpty($user->log);
    }

    public function testDeleteCustomer_noPayjpUser_returnsFalse(): void
    {
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $this->assertFalse($service->deleteCustomer(5));
    }

    // ============================================================
    // handleWebhook
    // ============================================================

    public function testHandleWebhook_oneTimeSuccess_confirmsChargeAndChargesPoint(): void
    {
        // fixture charge id=2: user1 / pending / cs_test_002
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $before = $this->pointUsers()->find()->where(['user_id' => 1])->first()->point;

        $event = [
            'type' => 'payment_flow.succeeded',
            'data' => [
                'id' => 'cs_test_002',
                'mode' => 'payment',
                'status' => 'succeeded',
                'payment_flow_id' => 'pf_confirmed_002',
                'payment_method_id' => 'pm_confirmed_002',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
            ],
        ];

        $this->assertTrue($service->handleWebhook($event));

        $charge = $this->payjpCharges()->get(2);
        $this->assertSame('success', $charge->status);
        $this->assertNotNull($charge->point_book_id);

        $after = $this->pointUsers()->find()->where(['user_id' => 1])->first()->point;
        $this->assertSame($before + 2000, $after);
    }

    public function testHandleWebhook_setupSuccess_activatesUser(): void
    {
        // setup の 仮登録 を作ってから webhook で確定する（interim status を固定しない）
        $api = $this->apiSuccess(['createCheckoutSession' => ['id' => 'cs_setup_900', 'url' => 'https://checkout.pay.jp/cs_setup_900']]);
        $service = new PayjpService($api);
        $service->createSetupCheckout(5, 9000, []);

        $event = [
            'type' => 'checkout_session.completed',
            'data' => [
                'id' => 'cs_setup_900',
                'mode' => 'setup',
                'status' => 'completed',
                'customer_id' => 'cus_setup_900',
                'payment_method_id' => 'pm_setup_900',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'user_id' => 5,
            ],
        ];

        $this->assertTrue($service->handleWebhook($event));

        $user = $this->payjpUsers()->find()->where(['user_id' => 5])->first();
        $this->assertNotNull($user);
        $this->assertSame('active', $user->status);
        $this->assertSame('pm_setup_900', $user->payjp_payment_method_code);
        $this->assertSame('cus_setup_900', $user->payjp_customer_code);
    }

    public function testHandleWebhook_failureEvent_marksFailureWithLog(): void
    {
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $event = [
            'type' => 'payment_flow.failed',
            'data' => [
                'id' => 'cs_test_002',
                'mode' => 'payment',
                'status' => 'failed',
                'failure_code' => 'card_declined',
            ],
        ];

        $this->assertTrue($service->handleWebhook($event));

        $charge = $this->payjpCharges()->get(2);
        $this->assertSame('failure', $charge->status);
        $this->assertNotEmpty($charge->log);
        $this->assertNull($charge->point_book_id);
    }

    public function testHandleWebhook_unknownEventType_returnsFalse(): void
    {
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $event = ['type' => 'some.unhandled.event', 'data' => ['id' => 'cs_test_002']];
        $this->assertFalse($service->handleWebhook($event));
    }

    public function testHandleWebhook_noMatchingRecord_returnsFalse(): void
    {
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $event = [
            'type' => 'payment_flow.succeeded',
            'data' => ['id' => 'cs_does_not_exist', 'mode' => 'payment', 'status' => 'succeeded'],
        ];
        $this->assertFalse($service->handleWebhook($event));
    }

    public function testHandleWebhook_alreadySuccess_doesNotChargePointTwice(): void
    {
        // fixture charge id=1: user1 / success / point_book_id=1 / cs_test_001
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $before = $this->pointUsers()->find()->where(['user_id' => 1])->first()->point;
        $originalPointBookId = $this->payjpCharges()->get(1)->point_book_id;

        $event = [
            'type' => 'payment_flow.succeeded',
            'data' => ['id' => 'cs_test_001', 'mode' => 'payment', 'status' => 'succeeded'],
        ];
        $service->handleWebhook($event);

        $after = $this->pointUsers()->find()->where(['user_id' => 1])->first()->point;
        $this->assertSame($before, $after, '二重チャージしない');
        $this->assertSame($originalPointBookId, $this->payjpCharges()->get(1)->point_book_id);
    }

    // ============================================================
    // handleWebhookById（event id を再取得して確定）
    // ============================================================

    public function testHandleWebhookById_refetchesEventAndConfirms(): void
    {
        // fixture charge id=2: user1 / pending / cs_test_002
        $api = $this->apiSuccess();
        $api->method('getEvent')->with('evnt_123')->willReturn([
            'type' => 'checkout_session.completed',
            'data' => [
                'id' => 'cs_test_002',
                'mode' => 'payment',
                'status' => 'completed',
                'payment_flow_id' => 'pf_confirmed_002',
                'payment_method_id' => 'pm_confirmed_002',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'user_id' => 1,
            ],
        ]);
        $service = new PayjpService($api);

        $this->assertTrue($service->handleWebhookById('evnt_123'));
        $this->assertSame('success', $this->payjpCharges()->get(2)->status);
    }

    public function testHandleWebhookById_getEventFalse_returnsFalse(): void
    {
        $api = $this->apiSuccess();
        $api->method('getEvent')->willReturn(false);
        $service = new PayjpService($api);

        $this->assertFalse($service->handleWebhookById('evnt_missing'));
    }

    public function testHandleWebhookById_emptyId_returnsFalse(): void
    {
        $api = $this->apiSuccess();
        $service = new PayjpService($api);

        $this->assertFalse($service->handleWebhookById(''));
    }

    // ============================================================
    // completeCheckout（success_url 到達時の補助確定）
    // ============================================================

    public function testCompleteCheckout_oneTimeSucceeded_confirmsCharge(): void
    {
        $api = $this->apiSuccess(['getCheckoutSession' => [
            'id' => 'cs_test_002',
            'mode' => 'payment',
            'status' => 'completed',
            'payment_flow_id' => 'pf_confirmed_002',
            'payment_method_id' => 'pm_confirmed_002',
            'card_brand' => 'Visa',
            'card_last4' => '4242',
            'user_id' => 1,
        ]]);
        $service = new PayjpService($api);

        $result = $service->completeCheckout('cs_test_002');

        $this->assertInstanceOf(PayjpCharge::class, $result);
        $this->assertSame('success', $this->payjpCharges()->get(2)->status);
        $this->assertNotNull($this->payjpCharges()->get(2)->point_book_id);
    }

    public function testCompleteCheckout_setupCompleted_confirmsUser(): void
    {
        $api = $this->apiSuccess(['createCheckoutSession' => ['id' => 'cs_setup_950', 'url' => 'https://checkout.pay.jp/cs_setup_950']]);
        $service = new PayjpService($api);
        $service->createSetupCheckout(5, 9500, []);

        // getCheckoutSession が setup 完了を返すよう差し替えた別モックで確定
        $api2 = $this->apiSuccess(['getCheckoutSession' => [
            'id' => 'cs_setup_950',
            'mode' => 'setup',
            'status' => 'completed',
            'customer_id' => 'cus_setup_950',
            'payment_method_id' => 'pm_setup_950',
            'card_brand' => 'Visa',
            'card_last4' => '4242',
            'user_id' => 5,
        ]]);
        $service2 = new PayjpService($api2);

        $result = $service2->completeCheckout('cs_setup_950');

        $this->assertInstanceOf(PayjpUser::class, $result);
        $user = $this->payjpUsers()->find()->where(['user_id' => 5])->first();
        $this->assertSame('active', $user->status);
        $this->assertSame('pm_setup_950', $user->payjp_payment_method_code);
    }

    public function testCompleteCheckout_notCompleted_returnsFalse(): void
    {
        $api = $this->apiSuccess(['getCheckoutSession' => [
            'id' => 'cs_test_002',
            'mode' => 'payment',
            'status' => 'open',
        ]]);
        $service = new PayjpService($api);

        $this->assertFalse($service->completeCheckout('cs_test_002'));
        $this->assertSame('pending', $this->payjpCharges()->get(2)->status);
    }

    public function testCompleteCheckout_noMatchingRecord_returnsFalse(): void
    {
        $api = $this->apiSuccess(['getCheckoutSession' => [
            'id' => 'cs_unknown',
            'mode' => 'payment',
            'status' => 'completed',
        ]]);
        $service = new PayjpService($api);

        $this->assertFalse($service->completeCheckout('cs_unknown'));
    }

    public function testCompleteCheckout_apiThrows_returnsFalse(): void
    {
        $api = $this->createMock(PayjpApiService::class);
        $api->method('getCheckoutSession')->willThrowException(new RuntimeException('api error'));
        $service = new PayjpService($api);

        $this->assertFalse($service->completeCheckout('cs_test_002'));
    }

    // ============================================================
    // generateIdempotencyKey（内部・間接検証）
    // ============================================================

    public function testGenerateIdempotencyKey_chargeHasUniqueKey(): void
    {
        $service = new PayjpService($this->apiSuccess());

        $charge = $service->chargeAuto(1);
        $this->assertInstanceOf(PayjpCharge::class, $charge);
        $this->assertNotEmpty($charge->idempotency_key);
        // UUID 形式（ハイフン区切り）を想定
        $this->assertMatchesRegularExpression('/[0-9a-f-]{16,}/i', $charge->idempotency_key);
    }
}
