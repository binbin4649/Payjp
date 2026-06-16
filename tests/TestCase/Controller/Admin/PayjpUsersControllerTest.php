<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Payjp\Controller\Admin\PayjpUsersController Test Case
 *
 * @uses \Payjp\Controller\Admin\PayjpUsersController
 */
class PayjpUsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'plugin.Payjp.PayjpUsers',
        'plugin.Payjp.Users',
        'plugin.Payjp.Companies',
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
        $this->get('/payjp/admin/payjp-users');
        $this->assertResponseOk();
        $this->assertNotNull($this->viewVariable('payjpUsers'));
    }

    public function testIndex_unauthenticated(): void
    {
        $this->get('/payjp/admin/payjp-users');
        $this->assertResponseCode(302);
    }

    public function testIndex_filterById(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users?id=1');
        $this->assertResponseOk();
        $this->assertNotNull($this->viewVariable('payjpUsers'));
    }

    public function testIndex_filterByStatus(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users?status=active');
        $this->assertResponseOk();
    }

    public function testIndex_filterByType(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users?type=auto_charge');
        $this->assertResponseOk();
    }

    // ---- view ----

    public function testView(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users/view/1');
        $this->assertResponseOk();
        $payjpUser = $this->viewVariable('payjpUser');
        $this->assertNotNull($payjpUser);
        $this->assertSame(1, $payjpUser->id);
    }

    public function testView_unauthenticated(): void
    {
        $this->get('/payjp/admin/payjp-users/view/1');
        $this->assertResponseCode(302);
    }

    public function testView_notFound(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users/view/9999');
        $this->assertResponseCode(404);
    }
}
