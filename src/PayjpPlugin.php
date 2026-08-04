<?php

declare(strict_types=1);

namespace Payjp;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;
use Payjp\Command\CardDeadlineCommand;
use Payjp\Command\SyncPendingCommand;
use Payjp\Service\PayjpService;

/**
 * Plugin for Payjp
 */
class PayjpPlugin extends BasePlugin
{
    public function bootstrap(PluginApplicationInterface $app): void {}

    public function routes(RouteBuilder $routes): void
    {
        parent::routes($routes);
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        return $middlewareQueue;
    }

    public function console(CommandCollection $commands): CommandCollection
    {
        $commands = parent::console($commands);

        return $commands;
    }

    public function services(ContainerInterface $container): void
    {
        // Controller のメソッドインジェクション（例: Nos\Controller\PointsController::purchase(PayjpService $payjp)）
        // を解決するために登録する。コンストラクタ引数はオプショナルのため引数定義は不要。
        $container->add(PayjpService::class);
        // 型付きコンストラクタを持つ Command は DI 登録しないと CommandFactory が
        // new $className($factory) で生成して TypeError になる（NosPlugin と同様）。
        $container->add(CardDeadlineCommand::class);
        $container->add(SyncPendingCommand::class);
    }
}
