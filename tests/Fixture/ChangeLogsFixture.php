<?php

declare(strict_types=1);

namespace Payjp\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ChangeLogsFixture
 *
 * notifyCardDeadline の送信済みガード（action=card_deadline）検証用。
 * 初期レコードなし。各テストで必要な行を作成する。
 */
class ChangeLogsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [];
        parent::init();
    }
}
