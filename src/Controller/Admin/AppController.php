<?php

declare(strict_types=1);

namespace Payjp\Controller\Admin;

use Payjp\Controller\AppController as BaseController;

class AppController extends BaseController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }
}
