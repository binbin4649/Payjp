<?php

declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;
use Payjp\Model\Entity\PayjpCharge;

class PayjpChargeTest extends TestCase
{
    public function testStatusConstant(): void
    {
        $this->assertArrayHasKey('success', PayjpCharge::STATUS);
        $this->assertArrayHasKey('failure', PayjpCharge::STATUS);
    }

    public function testTypeConstant(): void
    {
        $this->assertArrayHasKey('one_time', PayjpCharge::TYPE);
        $this->assertArrayHasKey('auto_charge', PayjpCharge::TYPE);
    }
}
