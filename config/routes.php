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
        $routes->fallbacks(DashedRoute::class);
    });
};
