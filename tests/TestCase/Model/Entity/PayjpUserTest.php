<?php

declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;
use Payjp\Model\Entity\PayjpUser;

class PayjpUserTest extends TestCase
{
    public function testStatusConstant(): void
    {
        $this->assertArrayHasKey('active', PayjpUser::STATUS);
        $this->assertArrayHasKey('suspended', PayjpUser::STATUS);
        $this->assertArrayHasKey('inactive', PayjpUser::STATUS);
        $this->assertArrayHasKey('failure', PayjpUser::STATUS);
        $this->assertArrayHasKey('deleted', PayjpUser::STATUS);
    }

    public function testTypeConstant(): void
    {
        $this->assertArrayHasKey('auto_charge', PayjpUser::TYPE);
        $this->assertArrayHasKey('other', PayjpUser::TYPE);
    }
}
