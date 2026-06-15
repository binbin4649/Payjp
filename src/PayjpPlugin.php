<?php

declare(strict_types=1);

namespace Payjp;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;

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

    public function services(ContainerInterface $container): void {}
}
