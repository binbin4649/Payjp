<?php
declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PointUsersFixture
 *
 * 決済成功時に PointService::charge() がポイントを加算する対象。
 * 1 company = 1 PointUser。user 1 は company 1、user 3（suspended 課金）は company 2。
 */
class PointUsersFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'company_id' => 1,
                'type' => 'prepaid',
                'point' => 1000,
                'credit' => 0,
                'created' => '2026-05-23 18:23:57',
                'modified' => '2026-05-23 18:23:57',
            ],
            [
                'id' => 2,
                'user_id' => 3,
                'company_id' => 2,
                'type' => 'prepaid',
                'point' => 500,
                'credit' => 0,
                'created' => '2026-05-23 18:23:57',
                'modified' => '2026-05-23 18:23:57',
            ],
        ];
        parent::init();
    }
}
