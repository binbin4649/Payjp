<?php

declare(strict_types=1);

use Cake\Core\Plugin;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Migrations\TestSuite\Migrator;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$pluginRoot = dirname(__DIR__);
$appRoot = dirname(dirname($pluginRoot));

if (!is_file($appRoot . DS . 'tests' . DS . 'bootstrap.php')) {
    throw new Exception('Cannot find application tests/bootstrap.php');
}

require_once $appRoot . DS . 'tests' . DS . 'bootstrap.php';

$cfgDir = $appRoot . DS . 'config' . DS;
if (is_file($cfgDir . 'app_local.php')) {
    Configure::config('default', new PhpConfig($cfgDir));
    Configure::load('app_local', 'default', true);
}

if (!Configure::check('App')) {
    $configDir = $appRoot . DS . 'config' . DS;
    Configure::config('app', new PhpConfig($configDir));
    Configure::load('app', 'app', false);
    if (is_file($configDir . 'app_local.php')) {
        Configure::load('app_local', 'app');
    }
}

$loader = require $appRoot . DS . 'vendor' . DS . 'autoload.php';
$loader->addPsr4('Payjp\\', $pluginRoot . DS . 'src' . DS);
$loader->addPsr4('Payjp\\Test\\', $pluginRoot . DS . 'tests' . DS);

$pointRoot = $appRoot . DS . 'plugins' . DS . 'Point' . DS;
$loader->addPsr4('Point\\', $pointRoot . 'src' . DS);
$loader->addPsr4('Point\\Test\\', $pointRoot . 'tests' . DS);

if (!Plugin::getCollection()->has('Point')) {
    Plugin::getCollection()->add(new \Point\PointPlugin([
        'name' => 'Point',
        'path' => $pointRoot,
    ]));
}

if (!Plugin::getCollection()->has('Payjp')) {
    Plugin::getCollection()->add(new \Payjp\PayjpPlugin([
        'name' => 'Payjp',
        'path' => $pluginRoot . DS,
    ]));
}

$migrator = new Migrator();
$migrator->runMany([
    ['connection' => 'test'],
    ['plugin' => 'Member', 'connection' => 'test'],
    ['plugin' => 'Point', 'connection' => 'test'],
    ['plugin' => 'Payjp', 'connection' => 'test'],
]);
