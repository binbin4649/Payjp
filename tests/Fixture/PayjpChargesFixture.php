<?php
declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PayjpChargesFixture
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
                'status' => 'Lorem ipsum dolor sit amet',
                'type' => 'Lorem ipsum dolor sit amet',
                'payjp_status' => 'Lorem ipsum dolor sit amet',
                'payjp_customer_code' => 'Lorem ipsum dolor sit amet',
                'ayjp_checkout_session_code' => 'Lorem ipsum dolor sit amet',
                'payjp_payment_flow_code' => 'Lorem ipsum dolor sit amet',
                'payjp_payment_method_code' => 'Lorem ipsum dolor sit amet',
                'amount' => 1,
                'card_brand' => 'Lorem ipsum dolor sit amet',
                'card_last4' => 'Lorem ipsum dolor sit amet',
                'idempotency_key' => 'Lorem ipsum dolor sit amet',
                'log' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2026-06-15 18:21:15',
                'modified' => '2026-06-15 18:21:15',
            ],
        ];
        parent::init();
    }
}
