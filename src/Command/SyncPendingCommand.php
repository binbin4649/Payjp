<?php

declare(strict_types=1);

namespace Payjp\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Payjp\Service\PayjpService;
use Throwable;

/**
 * 確定していない Checkout を PAY.JP と照合して確定する（webhook 未着の回収）。
 *
 * 決済確定の正本は webhook だが、success_url に戻ってこない・webhook が届かない場合に
 * pending のまま取り残される。cron から短い間隔で起動し、直近の pending を拾い直す。
 * completeCheckout() の冪等ガードにより再実行しても二重課金・二重加算は起きない。
 *
 * bin/cake payjp_sync_pending [hours]
 * cron は 5 分間隔（＊/5 * * * *）。全角＊はコメント終端を避けるための表記。
 */
class SyncPendingCommand extends Command
{
    public function __construct(private PayjpService $service = new PayjpService())
    {
        parent::__construct();
    }

    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('確定していない Checkout を PAY.JP と照合して確定する。');
        $parser->addArgument('hours', ['help' => '対象とする作成日時の遡り時間。省略時は 24']);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        try {
            $arg = $args->getArgument('hours');
            $hours = $arg !== null ? (int)$arg : 24;
            $result = $this->service->syncPendingCheckouts($hours);
            $io->success("照合 {$result['checked']} 件 / 確定 {$result['confirmed']} 件 / 失敗 {$result['failed']} 件");

            return $result['failed'] > 0 ? self::CODE_ERROR : self::CODE_SUCCESS;
        } catch (Throwable $e) {
            $io->error('SyncPendingCommand failed: ' . $e->getMessage());

            return self::CODE_ERROR;
        }
    }
}
