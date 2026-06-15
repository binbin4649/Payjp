<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Payjp\Controller\Admin\PayjpChargesController;

/**
 * Payjp\Controller\Admin\PayjpChargesController Test Case
 *
 * @uses \Payjp\Controller\Admin\PayjpChargesController
 */
class PayjpChargesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Payjp.PayjpCharges',
    ];

    /**
     * Test beforeFilter method
     *
     * @return void
     * @uses \Payjp\Controller\Admin\PayjpChargesController::beforeFilter()
     */
    public function testBeforeFilter(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \Payjp\Controller\Admin\PayjpChargesController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \Payjp\Controller\Admin\PayjpChargesController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \Payjp\Controller\Admin\PayjpChargesController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \Payjp\Controller\Admin\PayjpChargesController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \Payjp\Controller\Admin\PayjpChargesController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
