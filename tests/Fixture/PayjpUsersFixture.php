<?php
declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PayjpUsersFixture
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
                'status' => 'Lorem ipsum dolor sit amet',
                'type' => 'Lorem ipsum dolor sit amet',
                'auto_charge_amount' => 1,
                'payjp_card_token' => 'Lorem ipsum dolor sit amet',
                'payjp_customer_id' => 'Lorem ipsum dolor sit amet',
                'card_brand' => 'Lorem ipsum dolor sit amet',
                'card_last4' => 'Lorem ipsum dolor sit amet',
                'last_synced' => '2026-05-23 19:48:26',
                'log' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2026-05-23 19:48:26',
                'modified' => '2026-05-23 19:48:26',
            ],
        ];
        parent::init();
    }
}
