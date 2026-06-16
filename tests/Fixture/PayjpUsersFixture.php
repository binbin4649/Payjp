<?php
declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PayjpUsersFixture
 *
 * 顧客ステータス・PaymentMethod の有無を作り分けて finder / chargeAuto の遷移を検証する。
 *  - id=1 : active かつ payjp_payment_method_code あり（findActiveByUser 対象 / active→active）
 *  - id=2 : active だが payjp_payment_method_code が NULL（findActiveByUser 除外）
 *  - id=3 : suspended かつ pm あり（リトライ対象 / suspended→active|inactive）
 *  - id=4 : inactive（除外確認）
 */
class PayjpUsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'status' => 'active',
                'type' => 'auto_charge',
                'auto_charge_amount' => 10000,
                'payjp_customer_code' => 'cus_test_1',
                'payjp_payment_method_code' => 'pm_test_1',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'last_synced' => '2026-06-10 10:00:00',
                'log' => null,
                'created' => '2026-06-01 10:00:00',
                'modified' => '2026-06-10 10:00:00',
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'status' => 'active',
                'type' => 'auto_charge',
                'auto_charge_amount' => 5000,
                'payjp_customer_code' => 'cus_test_2',
                'payjp_payment_method_code' => null,
                'card_brand' => null,
                'card_last4' => null,
                'last_synced' => null,
                'log' => null,
                'created' => '2026-06-02 10:00:00',
                'modified' => '2026-06-02 10:00:00',
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'status' => 'suspended',
                'type' => 'auto_charge',
                'auto_charge_amount' => 8000,
                'payjp_customer_code' => 'cus_test_3',
                'payjp_payment_method_code' => 'pm_test_3',
                'card_brand' => 'Mastercard',
                'card_last4' => '5555',
                'last_synced' => '2026-06-08 10:00:00',
                'log' => 'previous auto charge failed',
                'created' => '2026-06-03 10:00:00',
                'modified' => '2026-06-08 10:00:00',
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'status' => 'inactive',
                'type' => 'auto_charge',
                'auto_charge_amount' => 3000,
                'payjp_customer_code' => 'cus_test_4',
                'payjp_payment_method_code' => 'pm_test_4',
                'card_brand' => 'JCB',
                'card_last4' => '0000',
                'last_synced' => '2026-06-07 10:00:00',
                'log' => 'auto charge stopped',
                'created' => '2026-06-04 10:00:00',
                'modified' => '2026-06-07 10:00:00',
            ],
        ];
        parent::init();
    }
}
