<?php

declare(strict_types=1);

namespace Payjp\Test\TestCase\Service;

use Cake\TestSuite\TestCase;
use Payjp\Model\Entity\PayjpCharge;
use Payjp\Model\Entity\PayjpUser;
use Payjp\Service\PayjpService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class PayjpServiceTest extends TestCase
{
    protected array $fixtures = [
        'plugin.Payjp.Users',
        'plugin.Payjp.PayjpUsers',
        'plugin.Payjp.PayjpCharges',
        'plugin.Payjp.PointUsers',
        'plugin.Payjp.PointBooks',
        'plugin.Point.Companies',
    ];

    private function makeService(array $onlyMethods = []): PayjpService
    {
        if (empty($onlyMethods)) {
            return new PayjpService();
        }

        return $this->getMockBuilder(PayjpService::class)
            ->onlyMethods($onlyMethods)
            ->getMock();
    }

    private function makeServiceWithApiSuccess(int $amount = 1000): PayjpService
    {
        $service = $this->getMockBuilder(PayjpService::class)
            ->onlyMethods(['payjpCreateCustomer', 'payjpCreateCharge'])
            ->getMock();

        $service->method('payjpCreateCustomer')->willReturn([
            'id' => 'cus_test_new',
        ]);

        $service->method('payjpCreateCharge')->willReturn([
            'id' => 'ch_test_new',
            'status' => 'captured',
            'amount' => $amount,
            'card' => ['brand' => 'Visa', 'last4' => '4242'],
        ]);

        return $service;
    }

    private function makeServiceWithApiFailure(): PayjpService
    {
        $service = $this->getMockBuilder(PayjpService::class)
            ->onlyMethods(['payjpCreateCustomer', 'payjpCreateCharge'])
            ->getMock();

        $service->method('payjpCreateCustomer')->willReturn(false);
        $service->method('payjpCreateCharge')->willReturn(false);

        return $service;
    }

    // ---- generateIdempotencyKey ----

    public function testGenerateIdempotencyKey_returnsNonEmptyString(): void
    {
        $service = new PayjpService();
        $key = $service->generateIdempotencyKey();
        $this->assertIsString($key);
        $this->assertNotEmpty($key);
    }

    public function testGenerateIdempotencyKey_returnsDifferentValues(): void
    {
        $service = new PayjpService();
        $key1 = $service->generateIdempotencyKey();
        $key2 = $service->generateIdempotencyKey();
        $this->assertNotSame($key1, $key2);
    }

    // ---- deleteCustomer ----

    public function testDeleteCustomer_updatesStatusToDeleted(): void
    {
        $service = new PayjpService();
        $result = $service->deleteCustomer(1);
        $this->assertInstanceOf(PayjpUser::class, $result);
        $this->assertSame('deleted', $result->status);
    }

    public function testDeleteCustomer_noUser_returnsFalse(): void
    {
        $service = new PayjpService();
        $result = $service->deleteCustomer(9999);
        $this->assertFalse($result);
    }

    // ---- registerCustomer ----

    public function testRegisterCustomer_apiSuccess_createsActiveRecord(): void
    {
        $service = $this->makeServiceWithApiSuccess();
        $result = $service->registerCustomer(1, 'auto_charge', 'tok_test_new', 1000, [
            'card_brand' => 'Visa',
            'card_last4' => '4242',
        ]);
        $this->assertInstanceOf(PayjpUser::class, $result);
        $this->assertSame('active', $result->status);
        $this->assertSame('cus_test_new', $result->payjp_customer_code);
    }

    public function testRegisterCustomer_apiFailure_createsFailureRecord(): void
    {
        $service = $this->makeServiceWithApiFailure();
        $result = $service->registerCustomer(1, 'auto_charge', 'tok_test_bad', 1000);
        $this->assertFalse($result);

        $payjpUsers = $this->getTableLocator()->get('Payjp.PayjpUsers');
        $record = $payjpUsers->find()->where(['user_id' => 1, 'status' => 'failure'])->first();
        $this->assertNotNull($record);
    }

    // ---- chargeOneTime ----

    public function testChargeOneTime_apiSuccess_returnsPayjpCharge(): void
    {
        $service = $this->makeServiceWithApiSuccess(1000);
        $result = $service->chargeOneTime(1, 1000, 'tok_test_active', [
            'card_brand' => 'Visa',
            'card_last4' => '4242',
        ]);
        $this->assertInstanceOf(PayjpCharge::class, $result);
        $this->assertSame('success', $result->status);
    }

    public function testChargeOneTime_apiSuccess_createsChargeRecord(): void
    {
        $service = $this->makeServiceWithApiSuccess(1000);
        $service->chargeOneTime(1, 1000, 'tok_test_active');

        $payjpCharges = $this->getTableLocator()->get('Payjp.PayjpCharges');
        $record = $payjpCharges->find()
            ->where(['user_id' => 1, 'status' => 'success', 'type' => 'one_time'])
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($record);
        $this->assertSame(1000, $record->amount);
    }

    public function testChargeOneTime_apiSuccess_addsPoint(): void
    {
        $service = $this->makeServiceWithApiSuccess(1000);
        $pointBefore = $this->getTableLocator()->get('Payjp.PointUsers')
            ->find()->where(['user_id' => 1])->first()->point;

        $service->chargeOneTime(1, 1000, 'tok_test_active');

        $pointAfter = $this->getTableLocator()->get('Payjp.PointUsers')
            ->find()->where(['user_id' => 1])->first()->point;
        $this->assertSame($pointBefore + 1000, $pointAfter);
    }

    public function testChargeOneTime_apiFailure_createsFailureRecord(): void
    {
        $service = $this->makeServiceWithApiFailure();
        $service->chargeOneTime(1, 1000, 'tok_test_bad');

        $payjpCharges = $this->getTableLocator()->get('Payjp.PayjpCharges');
        $record = $payjpCharges->find()
            ->where(['user_id' => 1, 'status' => 'failure'])
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($record);
    }

    public function testChargeOneTime_apiFailure_returnsFalse(): void
    {
        $service = $this->makeServiceWithApiFailure();
        $result = $service->chargeOneTime(1, 1000, 'tok_test_bad');
        $this->assertFalse($result);
    }

    // ---- chargeAuto ----

    public function testChargeAuto_activeUser_apiSuccess_returnsPayjpCharge(): void
    {
        $service = $this->makeServiceWithApiSuccess(1000);
        $result = $service->chargeAuto(1);
        $this->assertInstanceOf(PayjpCharge::class, $result);
        $this->assertSame('success', $result->status);
        $this->assertSame('auto_charge', $result->type);
    }

    public function testChargeAuto_activeUser_apiSuccess_statusRemainsActive(): void
    {
        $service = $this->makeServiceWithApiSuccess(1000);
        $service->chargeAuto(1);

        $payjpUsers = $this->getTableLocator()->get('Payjp.PayjpUsers');
        $user = $payjpUsers->find()->where(['user_id' => 1])->first();
        $this->assertSame('active', $user->status);
    }

    public function testChargeAuto_activeUser_apiSuccess_addsPoint(): void
    {
        $service = $this->makeServiceWithApiSuccess(1000);
        $pointBefore = $this->getTableLocator()->get('Payjp.PointUsers')
            ->find()->where(['user_id' => 1])->first()->point;

        $service->chargeAuto(1);

        $pointAfter = $this->getTableLocator()->get('Payjp.PointUsers')
            ->find()->where(['user_id' => 1])->first()->point;
        $this->assertSame($pointBefore + 1000, $pointAfter);
    }

    public function testChargeAuto_suspendedUser_apiSuccess_statusBecomesActive(): void
    {
        // id=2 is suspended with auto_charge_amount=500
        $service = $this->makeServiceWithApiSuccess(500);
        $service->chargeAuto(2);

        $payjpUsers = $this->getTableLocator()->get('Payjp.PayjpUsers');
        $user = $payjpUsers->find()->where(['user_id' => 2])->first();
        $this->assertSame('active', $user->status);
    }

    public function testChargeAuto_apiFailure_statusUnchanged(): void
    {
        $service = $this->makeServiceWithApiFailure();
        $service->chargeAuto(1);

        $payjpUsers = $this->getTableLocator()->get('Payjp.PayjpUsers');
        $user = $payjpUsers->find()->where(['user_id' => 1])->first();
        $this->assertSame('active', $user->status);
    }

    public function testChargeAuto_apiFailure_createsFailureRecord(): void
    {
        $service = $this->makeServiceWithApiFailure();
        $service->chargeAuto(1);

        $payjpCharges = $this->getTableLocator()->get('Payjp.PayjpCharges');
        $record = $payjpCharges->find()
            ->where(['user_id' => 1, 'status' => 'failure', 'type' => 'auto_charge'])
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($record);
    }

    public function testChargeAuto_noActiveUser_returnsFalse(): void
    {
        $service = $this->makeServiceWithApiSuccess();
        $result = $service->chargeAuto(9999);
        $this->assertFalse($result);
    }
}
