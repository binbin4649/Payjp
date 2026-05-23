<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Payjp\Model\Table\PayjpChargesTable;

/**
 * Payjp\Model\Table\PayjpChargesTable Test Case
 */
class PayjpChargesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Payjp\Model\Table\PayjpChargesTable
     */
    protected $PayjpCharges;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Payjp.PayjpCharges',
        'plugin.Payjp.Users',
        'plugin.Payjp.PointBooks',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PayjpCharges') ? [] : ['className' => PayjpChargesTable::class];
        $this->PayjpCharges = $this->getTableLocator()->get('PayjpCharges', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PayjpCharges);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \Payjp\Model\Table\PayjpChargesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \Payjp\Model\Table\PayjpChargesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
