<?php

declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EmailTemplatesFixture
 *
 * notifyCardDeadline の送信テスト用に mail_type=card_deadline の active テンプレートを持つ。
 */
class EmailTemplatesFixture extends TestFixture
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
                'title' => '{site_name} クレジットカード有効期限のお知らせ',
                'body' => '{name}様

ご登録のクレジットカードは、今月末で有効期限を迎えます。
',
                'footer' => 'sample footer',
                'email_transport' => 'default',
                'status' => 'active',
                'mail_type' => 'card_deadline',
                'is_display' => 1,
                'is_super' => 1,
                'created' => '2026-07-01 10:00:00',
                'modified' => '2026-07-01 10:00:00',
            ],
        ];
        parent::init();
    }
}
