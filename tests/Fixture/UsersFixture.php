<?php

declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    public array $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'autoIncrement' => true],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'company_id' => ['type' => 'integer', 'length' => 11, 'null' => true],
        'status' => ['type' => 'string', 'length' => 255, 'null' => false],
        'role' => ['type' => 'string', 'length' => 255, 'null' => false],
        'position_name' => ['type' => 'string', 'length' => 255, 'null' => true],
        'username' => ['type' => 'string', 'length' => 255, 'null' => false],
        'password' => ['type' => 'string', 'length' => 255, 'null' => false],
        'auth_type' => ['type' => 'string', 'length' => 255, 'null' => true],
        'auth_code' => ['type' => 'string', 'length' => 255, 'null' => true],
        'access_token' => ['type' => 'string', 'length' => 255, 'null' => true],
        'refresh_token' => ['type' => 'string', 'length' => 255, 'null' => true],
        'notice_type' => ['type' => 'string', 'length' => 255, 'null' => false],
        'email' => ['type' => 'string', 'length' => 255, 'null' => false],
        'gender' => ['type' => 'string', 'length' => 255, 'null' => true],
        'job' => ['type' => 'string', 'length' => 255, 'null' => true],
        'tel' => ['type' => 'string', 'length' => 255, 'null' => true],
        'zip' => ['type' => 'string', 'length' => 255, 'null' => true],
        'prefectures' => ['type' => 'integer', 'length' => 11, 'null' => true],
        'address_1' => ['type' => 'string', 'length' => 255, 'null' => true],
        'address_2' => ['type' => 'string', 'length' => 255, 'null' => true],
        'magiclink' => ['type' => 'string', 'length' => 255, 'null' => true],
        'is_magiclink' => ['type' => 'integer', 'length' => 1, 'null' => false],
        'memo' => ['type' => 'text', 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'email' => ['type' => 'unique', 'columns' => ['email']],
        ],
    ];

    public function init(): void
    {
        $hasher = new DefaultPasswordHasher();
        $this->records = [
            [
                'id' => 1,
                'name' => 'test',
                'company_id' => 1,
                'status' => 'active',
                'role' => 'user',
                'position_name' => null,
                'username' => 'test',
                'password' => $hasher->hash('test'),
                'auth_type' => 'email',
                'auth_code' => null,
                'access_token' => null,
                'refresh_token' => null,
                'notice_type' => 'email',
                'email' => 'test@example.com',
                'gender' => 'men',
                'job' => null,
                'tel' => '09012345678',
                'zip' => '1234567',
                'prefectures' => 13,
                'address_1' => '東京都千代田区永田町1-7-1',
                'address_2' => '101',
                'magiclink' => null,
                'is_magiclink' => 0,
                'memo' => null,
                'created' => '2026-01-01 10:00:00',
                'modified' => '2026-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'name' => 'test2',
                'company_id' => 2,
                'status' => 'active',
                'role' => 'user',
                'position_name' => null,
                'username' => 'test2',
                'password' => $hasher->hash('test2'),
                'auth_type' => 'email',
                'auth_code' => null,
                'access_token' => null,
                'refresh_token' => null,
                'notice_type' => 'email',
                'email' => 'test2@example.com',
                'gender' => 'women',
                'job' => null,
                'tel' => '09012345679',
                'zip' => '1234568',
                'prefectures' => 13,
                'address_1' => '東京都千代田区永田町1-7-1',
                'address_2' => '201',
                'magiclink' => null,
                'is_magiclink' => 0,
                'memo' => null,
                'created' => '2026-01-01 10:00:00',
                'modified' => '2026-01-01 10:00:00',
            ],
        ];
        parent::init();
    }
}
