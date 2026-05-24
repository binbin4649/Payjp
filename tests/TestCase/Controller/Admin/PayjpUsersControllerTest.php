<?php

declare(strict_types=1);

namespace Payjp\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class PayjpUsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'plugin.Payjp.PayjpUsers',
        'plugin.Payjp.Users',
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
        $payjpUsers = $this->viewVariable('payjpUsers');
        $this->assertNotNull($payjpUsers);
    }

    public function testIndex_unauthenticated(): void
    {
        $this->get('/payjp/admin/payjp-users');
        $this->assertResponseCode(302);
    }

    public function testIndex_filterByUserId(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users?user_id=1');
        $this->assertResponseOk();
        $payjpUsers = $this->viewVariable('payjpUsers');
        $this->assertNotNull($payjpUsers);
        foreach ($payjpUsers as $payjpUser) {
            $this->assertSame(1, $payjpUser->user_id);
        }
    }

    public function testIndex_filterByStatus(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users?status=active');
        $this->assertResponseOk();
        $payjpUsers = $this->viewVariable('payjpUsers');
        $this->assertNotNull($payjpUsers);
        foreach ($payjpUsers as $payjpUser) {
            $this->assertSame('active', $payjpUser->status);
        }
    }

    public function testIndex_filterByType(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users?type=auto_charge');
        $this->assertResponseOk();
        $payjpUsers = $this->viewVariable('payjpUsers');
        $this->assertNotNull($payjpUsers);
        foreach ($payjpUsers as $payjpUser) {
            $this->assertSame('auto_charge', $payjpUser->type);
        }
    }

    public function testIndex_setsStatuses(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users');
        $this->assertResponseOk();
        $statuses = $this->viewVariable('statuses');
        $this->assertNotNull($statuses);
    }

    public function testIndex_setsTypes(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-users');
        $this->assertResponseOk();
        $types = $this->viewVariable('types');
        $this->assertNotNull($types);
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
