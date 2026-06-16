<?php
declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PointUsersFixture
 *
 * 決済成功時に PointService::charge() がポイントを加算する対象。user 1〜3 に prepaid 残高を用意する。
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
                'user_id' => 2,
                'company_id' => 1,
                'type' => 'prepaid',
                'point' => 1000,
                'credit' => 0,
                'created' => '2026-05-23 18:23:57',
                'modified' => '2026-05-23 18:23:57',
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'company_id' => 1,
                'type' => 'prepaid',
                'point' => 500,
                'credit' => 0,
                'created' => '2026-05-23 18:23:57',
                'modified' => '2026-05-23 18:23:57',
            ],
            [
                'id' => 4,
                'user_id' => 5,
                'company_id' => 1,
                'type' => 'prepaid',
                'point' => 0,
                'credit' => 0,
                'created' => '2026-05-23 18:23:57',
                'modified' => '2026-05-23 18:23:57',
            ],
        ];
        parent::init();
    }
}
