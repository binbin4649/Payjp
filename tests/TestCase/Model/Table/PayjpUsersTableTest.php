<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Payjp\Model\Table\PayjpUsersTable;

/**
 * Payjp\Model\Table\PayjpUsersTable Test Case
 */
class PayjpUsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Payjp\Model\Table\PayjpUsersTable
     */
    protected $PayjpUsers;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Payjp.PayjpUsers',
        'plugin.Payjp.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PayjpUsers') ? [] : ['className' => PayjpUsersTable::class];
        $this->PayjpUsers = $this->getTableLocator()->get('PayjpUsers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PayjpUsers);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \Payjp\Model\Table\PayjpUsersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \Payjp\Model\Table\PayjpUsersTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
