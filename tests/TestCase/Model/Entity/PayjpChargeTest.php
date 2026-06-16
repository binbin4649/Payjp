<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;
use Payjp\Model\Entity\PayjpCharge;

/**
 * Payjp\Model\Entity\PayjpCharge Test Case
 *
 * @uses \Payjp\Model\Entity\PayjpCharge
 */
class PayjpChargeTest extends TestCase
{
    // ---- STATUS ----

    public function testStatusConstant_matchesSpec(): void
    {
        $expected = [
            'pending' => '処理待ち',
            'processing' => '処理中',
            'success' => '決済成功',
            'failure' => '決済失敗',
        ];
        $this->assertSame($expected, PayjpCharge::STATUS);
    }

    public function testStatusConstant_keys(): void
    {
        $this->assertSame(
            ['pending', 'processing', 'success', 'failure'],
            array_keys(PayjpCharge::STATUS)
        );
    }

    // ---- TYPE ----

    public function testTypeConstant_matchesSpec(): void
    {
        $expected = [
            'one_time' => '都度課金',
            'auto_charge' => 'オートチャージ',
        ];
        $this->assertSame($expected, PayjpCharge::TYPE);
    }

    public function testTypeConstant_keys(): void
    {
        $this->assertSame(['one_time', 'auto_charge'], array_keys(PayjpCharge::TYPE));
    }
}
