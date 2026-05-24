<?php

declare(strict_types=1);

namespace Payjp\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class PayjpChargesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'plugin.Payjp.PayjpCharges',
        'plugin.Payjp.Users',
        'plugin.Payjp.PointBooks',
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
        $payjpCharges = $this->viewVariable('payjpCharges');
        $this->assertNotNull($payjpCharges);
    }

    public function testIndex_unauthenticated(): void
    {
        $this->get('/payjp/admin/payjp-charges');
        $this->assertResponseCode(302);
    }

    public function testIndex_filterByUserId(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges?user_id=1');
        $this->assertResponseOk();
        $payjpCharges = $this->viewVariable('payjpCharges');
        $this->assertNotNull($payjpCharges);
        foreach ($payjpCharges as $charge) {
            $this->assertSame(1, $charge->user_id);
        }
    }

    public function testIndex_filterByStatus(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges?status=success');
        $this->assertResponseOk();
        $payjpCharges = $this->viewVariable('payjpCharges');
        $this->assertNotNull($payjpCharges);
        foreach ($payjpCharges as $charge) {
            $this->assertSame('success', $charge->status);
        }
    }

    public function testIndex_filterByType(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges?type=one_time');
        $this->assertResponseOk();
        $payjpCharges = $this->viewVariable('payjpCharges');
        $this->assertNotNull($payjpCharges);
        foreach ($payjpCharges as $charge) {
            $this->assertSame('one_time', $charge->type);
        }
    }

    public function testIndex_setsStatuses(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges');
        $this->assertResponseOk();
        $statuses = $this->viewVariable('statuses');
        $this->assertNotNull($statuses);
    }

    public function testIndex_setsTypes(): void
    {
        $this->loginAsAdmin();
        $this->get('/payjp/admin/payjp-charges');
        $this->assertResponseOk();
        $types = $this->viewVariable('types');
        $this->assertNotNull($types);
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
