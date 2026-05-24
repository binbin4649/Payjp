<?php

declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PayjpChargesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'point_book_id' => 1,
                'status' => 'success',
                'type' => 'one_time',
                'payjp_status' => 'captured',
                'payjp_customer_code' => null,
                'payjp_charge_code' => 'ch_test_0001',
                'amount' => 1000,
                'payjp_card_token' => 'tok_test_active',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'idempotency_key' => 'idem-0001-0001',
                'log' => null,
                'created' => '2026-01-01 10:00:00',
                'modified' => '2026-01-01 10:00:00',
            ],
        ];
        parent::init();
    }
}
