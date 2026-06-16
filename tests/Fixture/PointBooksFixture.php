<?php
declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PointBooksFixture
 *
 * payjp_charges.point_book_id の参照先。確定済み決済（success）が紐付く台帳。
 */
class PointBooksFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'company_id' => 1,
                'app_name' => 'Nos',
                'foreign_model' => null,
                'foreign_id' => null,
                'action' => 'charge',
                'charge_type' => 'payjp',
                'reason_code' => 'monthly_plan',
                'reason' => null,
                'point' => 1000,
                'credit' => 0,
                'point_balance' => 1000,
                'credit_balance' => 0,
                'created' => '2026-06-01 10:05:00',
                'modified' => '2026-06-01 10:05:00',
            ],
        ];
        parent::init();
    }
}
