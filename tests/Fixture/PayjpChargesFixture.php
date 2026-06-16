<?php
declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PayjpChargesFixture
 *
 *  - id=1 : user 1 / one_time / success（point_book_id 紐付け済み）古い
 *  - id=2 : user 1 / one_time / pending（point_book_id NULL、webhook 確定待ち）新しい
 *  - id=3 : user 2 / auto_charge / failure
 *
 * findByUser(1) は新しい順で id=2, id=1。findByCheckoutSession('cs_test_002') は id=2。
 */
class PayjpChargesFixture extends TestFixture
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
                'point_book_id' => 1,
                'status' => 'success',
                'type' => 'one_time',
                'payjp_status' => 'succeeded',
                'payjp_customer_code' => null,
                'payjp_checkout_session_code' => 'cs_test_001',
                'payjp_payment_flow_code' => 'pf_test_001',
                'payjp_payment_method_code' => 'pm_test_001',
                'amount' => 1000,
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'idempotency_key' => 'idem_test_001',
                'log' => null,
                'created' => '2026-06-01 10:00:00',
                'modified' => '2026-06-01 10:05:00',
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'point_book_id' => null,
                'status' => 'pending',
                'type' => 'one_time',
                'payjp_status' => null,
                'payjp_customer_code' => null,
                'payjp_checkout_session_code' => 'cs_test_002',
                'payjp_payment_flow_code' => null,
                'payjp_payment_method_code' => null,
                'amount' => 2000,
                'card_brand' => null,
                'card_last4' => null,
                'idempotency_key' => 'idem_test_002',
                'log' => null,
                'created' => '2026-06-10 10:00:00',
                'modified' => '2026-06-10 10:00:00',
            ],
            [
                'id' => 3,
                'user_id' => 2,
                'point_book_id' => null,
                'status' => 'failure',
                'type' => 'auto_charge',
                'payjp_status' => 'canceled',
                'payjp_customer_code' => 'cus_test_2',
                'payjp_checkout_session_code' => 'cs_test_003',
                'payjp_payment_flow_code' => 'pf_test_003',
                'payjp_payment_method_code' => 'pm_test_2',
                'amount' => 5000,
                'card_brand' => null,
                'card_last4' => null,
                'idempotency_key' => 'idem_test_003',
                'log' => 'card_declined',
                'created' => '2026-06-05 10:00:00',
                'modified' => '2026-06-05 10:05:00',
            ],
        ];
        parent::init();
    }
}
