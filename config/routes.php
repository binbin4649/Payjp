<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes): void {
    $routes->plugin('Payjp', ['path' => '/payjp'], function (RouteBuilder $routes) {
        $routes->setRouteClass(DashedRoute::class);
        $routes->prefix('Admin', function (RouteBuilder $routes) {
            $routes->setRouteClass(DashedRoute::class);
            $routes->fallbacks(DashedRoute::class);
        });

        // PAY.JP からの webhook 受信（固定 URL を確保）。
        // メソッド制限はコントローラーの allowMethod に委ね、非 POST は 405 を返す
        // （fallback に落として MissingController エラーを誘発しない）。
        $routes->connect('/webhook', ['controller' => 'PayjpWebhooks', 'action' => 'index']);
        // Checkout の success_url 到達時の確定（メソッド制限はコントローラー側）。
        $routes->connect('/complete', ['controller' => 'Payments', 'action' => 'complete']);

        $routes->fallbacks(DashedRoute::class);
    });
};
