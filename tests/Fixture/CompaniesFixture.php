<?php

declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CompaniesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'company1',
                'parent_id' => 1,
                'status' => 'active',
                'type' => 'head',
                'is_notice' => 1,
                'emails' => 'company1@example.com',
                'tel' => '0312345678',
                'department_name' => 'company1 department',
                'zip' => '1000014',
                'prefectures' => 13,
                'address_1' => '東京都千代田区永田町1-7-1',
                'address_2' => '101',
                'memo' => 'company1 memo',
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                'id' => 2,
                'name' => 'company2',
                'parent_id' => null,
                'status' => 'active',
                'type' => 'branch',
                'is_notice' => 1,
                'emails' => 'company2@example.com',
                'tel' => '0312345679',
                'department_name' => 'company2 department',
                'zip' => '1000015',
                'prefectures' => 13,
                'address_1' => '東京都千代田区永田町1-7-2',
                'address_2' => '201',
                'memo' => 'company2 memo',
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
        ];
        parent::init();
    }
}
