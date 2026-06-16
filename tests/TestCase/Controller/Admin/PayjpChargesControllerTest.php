<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Payjp\Controller\Admin\PayjpChargesController Test Case
 *
 * @uses \Payjp\Controller\Admin\PayjpChargesController
 */
class PayjpChargesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'plugin.Payjp.PayjpCharges',
        'plugin.Payjp.Users',
        'plugin.Payjp.Companies',
        'plugin.Payjp.PointBooks',
        'plugin.Payjp.PointUsers',
        'plugin.Member.Admins',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function loginAsAdmin(): void
    {
        $adminsTable = $this->getTableLocator()->get('Member.Admins');
        $admin = $adminsTable->get(1);
        $this->session(['Auth.Admin' => $admin->toArray()]);
    }

    // ---- index ----

    public function testIndex(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges');
        $this->assertResponseOk();
        $this->assertNotNull($this->viewVariable('payjpCharges'));
    }

    public function testIndex_unauthenticated(): void
    {
        $this->get('/payjp/admin/payjp-charges');
        $this->assertResponseCode(302);
    }

    public function testIndex_filterById(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges?id=1');
        $this->assertResponseOk();
        $this->assertNotNull($this->viewVariable('payjpCharges'));
    }

    public function testIndex_filterByStatus(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges?status=success');
        $this->assertResponseOk();
    }

    public function testIndex_filterByType(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges?type=one_time');
        $this->assertResponseOk();
    }

    // ---- view ----

    public function testView(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges/view/1');
        $this->assertResponseOk();
        $payjpCharge = $this->viewVariable('payjpCharge');
        $this->assertNotNull($payjpCharge);
        $this->assertSame(1, $payjpCharge->id);
    }

    public function testView_unauthenticated(): void
    {
        $this->get('/payjp/admin/payjp-charges/view/1');
        $this->assertResponseCode(302);
    }

    public function testView_notFound(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges/view/9999');
        $this->assertResponseCode(404);
    }
}
