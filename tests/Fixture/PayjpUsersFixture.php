<?php

declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PayjpUsersFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'status' => 'active',
                'type' => 'auto_charge',
                'auto_charge_amount' => 1000,
                'payjp_card_token' => 'tok_test_active',
                'payjp_customer_code' => 'cus_test_active',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'last_synced' => '2026-01-01 10:00:00',
                'log' => null,
                'created' => '2026-01-01 10:00:00',
                'modified' => '2026-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'status' => 'suspended',
                'type' => 'auto_charge',
                'auto_charge_amount' => 500,
                'payjp_card_token' => 'tok_test_suspended',
                'payjp_customer_code' => 'cus_test_suspended',
                'card_brand' => 'Mastercard',
                'card_last4' => '5555',
                'last_synced' => '2026-01-01 10:00:00',
                'log' => null,
                'created' => '2026-01-01 10:00:00',
                'modified' => '2026-01-01 10:00:00',
            ],
        ];
        parent::init();
    }
}
