<?php

declare(strict_types=1);

namespace Payjp\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Log\Log;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\Date;
use Payjp\Service\PayjpService;
use Throwable;

/**
 * カード有効期限が当月のユーザーへ期限切れ予告メールを送信する。
 *
 * cron から毎日1回起動する想定（例: 0 10 * * *）。change_logs の送信済みガードにより
 * 同一ユーザーへの送信は月1回に冪等化されるため、再実行・毎日実行しても安全。
 *
 * bin/cake card_deadline [YYYY-MM]
 * 0 10 * * * cd /path/to/app && bin/cake card_deadline
 */
class CardDeadlineCommand extends Command
{
    public function __construct(private PayjpService $service = new PayjpService())
    {
        parent::__construct();
    }

    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('カード有効期限が当月のユーザーへ期限切れ予告メールを送信する。');
        $parser->addArgument('target_month', ['help' => '対象月（YYYY-MM）。省略時は当月']);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        try {
            $arg = $args->getArgument('target_month');
            $targetMonth = $arg !== null ? Date::parse($arg . '-01') : Date::today();
            $result = $this->service->notifyCardDeadline($targetMonth);
            $io->success("送信 {$result['sent']} 件 / スキップ {$result['skipped']} 件 / 失敗 {$result['failed']} 件");

            $month = $targetMonth->format('Y-m');
            if ($result['failed'] > 0) {
                Log::error(
                    'CardDeadlineCommand::execute completed with failures:'
                    . ' target_month=' . $month
                    . ' sent=' . $result['sent']
                    . ' skipped=' . $result['skipped']
                    . ' failed=' . $result['failed'],
                );

                return self::CODE_ERROR;
            }
            Log::info(
                'CardDeadlineCommand::execute done:'
                . ' target_month=' . $month
                . ' sent=' . $result['sent']
                . ' skipped=' . $result['skipped'],
            );

            return self::CODE_SUCCESS;
        } catch (Throwable $e) {
            Log::error('CardDeadlineCommand::execute failed: ' . $e->getMessage());
            $io->error('CardDeadlineCommand failed: ' . $e->getMessage());

            return self::CODE_ERROR;
        }
    }
}
