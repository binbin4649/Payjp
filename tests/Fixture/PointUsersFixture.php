<?php

declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PointUsersFixture extends TestFixture
{
    public string $table = 'point_users';

    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'company_id' => 1,
                'type' => 'prepaid',
                'point' => 10000,
                'credit' => 0,
                'created' => '2026-01-01 10:00:00',
                'modified' => '2026-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'company_id' => 1,
                'type' => 'prepaid',
                'point' => 5000,
                'credit' => 0,
                'created' => '2026-01-01 10:00:00',
                'modified' => '2026-01-01 10:00:00',
            ],
        ];
        parent::init();
    }
}
