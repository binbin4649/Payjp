<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;
use Payjp\Model\Entity\PayjpUser;

/**
 * Payjp\Model\Entity\PayjpUser Test Case
 *
 * @uses \Payjp\Model\Entity\PayjpUser
 */
class PayjpUserTest extends TestCase
{
    // ---- STATUS ----

    public function testStatusConstant_matchesSpec(): void
    {
        $expected = [
            'active' => '正常稼働',
            'suspended' => 'リトライ待ち',
            'inactive' => '停止',
            'failure' => '失敗',
            'deleted' => '退会済み',
        ];
        $this->assertSame($expected, PayjpUser::STATUS);
    }

    public function testStatusConstant_keys(): void
    {
        $this->assertSame(
            ['active', 'suspended', 'inactive', 'failure', 'deleted'],
            array_keys(PayjpUser::STATUS)
        );
    }

    // ---- TYPE ----

    public function testTypeConstant_matchesSpec(): void
    {
        $expected = [
            'auto_charge' => 'オートチャージ',
            'other' => 'その他',
        ];
        $this->assertSame($expected, PayjpUser::TYPE);
    }

    public function testTypeConstant_keys(): void
    {
        $this->assertSame(['auto_charge', 'other'], array_keys(PayjpUser::TYPE));
    }
}
