<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Payjp\Command\SyncPendingCommand Test Case
 *
 * 型付きコンストラクタを持つ Command は PayjpPlugin::services() に DI 登録しないと
 * CommandFactory が TypeError になる。ここではその解決と委譲を確認する。
 * fixture の pending 行はいずれも作成日時が古く対象ウィンドウ外なので、
 * PAY.JP API は呼ばれない（外部通信なしで完結する）。
 *
 * @uses \Payjp\Command\SyncPendingCommand
 */
class SyncPendingCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected array $fixtures = [
        'plugin.Payjp.PayjpUsers',
        'plugin.Payjp.PayjpCharges',
        'plugin.Payjp.Users',
        'plugin.Payjp.Companies',
        'plugin.Payjp.PointBooks',
        'plugin.Payjp.PointUsers',
    ];

    public function testRunsAndReportsCounts(): void
    {
        $this->exec('payjp.sync_pending 1');

        $this->assertExitSuccess();
        $this->assertOutputContains('照合 0 件');
    }

    public function testAcceptsDefaultHours(): void
    {
        $this->exec('payjp.sync_pending');

        $this->assertExitSuccess();
    }
}
