<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Controller;

use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Payjp\Controller\PaymentsController;
use Payjp\Model\Entity\PayjpCharge;
use Payjp\Service\PayjpService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use ReflectionProperty;

/**
 * Payjp\Controller\PaymentsController Test Case
 *
 * 外部 API（PayjpService::completeCheckout）を叩かないよう PayjpService をモックに差し替える。
 * テンプレートレンダリングは ajax レイアウトに切り替えて軽量化する。
 *
 * @uses \Payjp\Controller\PaymentsController
 */
#[AllowMockObjectsWithoutExpectations]
class PaymentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * status は要ログインで、セッション認証が users から identity を引き直すため
     * 実在するユーザー行が要る。
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Payjp.Users',
        'plugin.Payjp.Companies',
    ];

    /**
     * Controller.initialize で mock サービスを注入し、レイアウトを ajax に切り替える。
     */
    private function mockService(PayjpService $mock): void
    {
        EventManager::instance()->on('Controller.initialize', function (EventInterface $event) use ($mock): void {
            $controller = $event->getSubject();
            if ($controller instanceof PaymentsController) {
                (new ReflectionProperty($controller, 'payjpService'))->setValue($controller, $mock);
                $controller->viewBuilder()->setLayout('ajax');
            }
        });
    }

    public function testCompletesAndRedirectsToSafePath(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->expects($this->once())
            ->method('completeCheckout')
            ->with('cs_abc')
            ->willReturn(new PayjpCharge());
        $this->mockService($mock);

        $this->get('/payjp/complete?session_id=cs_abc&redirect=/mypage');

        $this->assertResponseCode(302);
        $this->assertRedirect('/mypage');
    }

    public function testRendersTemplateWhenNoRedirect(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->method('completeCheckout')->with('cs_abc')->willReturn(new PayjpCharge());
        $this->mockService($mock);

        $this->get('/payjp/complete?session_id=cs_abc');

        $this->assertResponseOk();
        $this->assertTrue($this->viewVariable('completed'));
    }

    public function testUnsafeRedirectIsIgnored(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->method('completeCheckout')->willReturn(new PayjpCharge());
        $this->mockService($mock);

        $this->get('/payjp/complete?session_id=cs_abc&redirect=https://evil.example.com');

        // 絶対 URL（オープンリダイレクト）を拒否し、テンプレート表示にフォールバックする
        $this->assertResponseOk();
        $this->assertNoRedirect();
    }

    public function testRejectsNonGetMethod(): void
    {
        // ルートはコントローラーに到達し、allowMethod が非 GET を 405 で返す。
        $this->post('/payjp/complete', ['session_id' => 'cs_abc']);
        $this->assertResponseCode(405);
    }

    public function testNoSessionIdDoesNotCallService(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->expects($this->never())->method('completeCheckout');
        $this->mockService($mock);

        $this->get('/payjp/complete');

        $this->assertResponseOk();
        $this->assertFalse($this->viewVariable('completed'));
    }

    // ============================================================
    // status（ローディング画面のポーリング）
    // ============================================================

    private function loginAsUser(int $id = 1): void
    {
        $this->session(['Auth.User' => ['id' => $id, 'username' => 'test' . $id]]);
    }

    /**
     * 未ログインでは状態を返さない（他人の決済状態を覗かせない）。
     */
    public function testStatusRequiresAuthentication(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->expects($this->never())->method('checkoutStatus');
        $this->mockService($mock);

        $this->get('/payjp/status?session_id=cs_abc');

        $this->assertResponseCode(302);
    }

    public function testStatusReturnsJson(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->expects($this->once())
            ->method('checkoutStatus')
            ->with('cs_abc', 1)
            ->willReturn(['state' => 'success', 'kind' => 'payment']);
        $this->mockService($mock);
        $this->loginAsUser();

        $this->get('/payjp/status?session_id=cs_abc');

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $this->assertSame(['state' => 'success', 'kind' => 'payment'], json_decode((string)$this->_response->getBody(), true));
    }

    /**
     * 通常のポーリングでは PAY.JP API を叩かない（2 秒間隔で呼ばれるため）。
     */
    public function testStatusDoesNotCallApiWithoutFinal(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->method('checkoutStatus')->willReturn(['state' => 'pending', 'kind' => 'payment']);
        $mock->expects($this->never())->method('completeCheckout');
        $this->mockService($mock);
        $this->loginAsUser();

        $this->get('/payjp/status?session_id=cs_abc');

        $this->assertResponseOk();
    }

    /**
     * final=1（30 秒タイムアウト時）は保険として completeCheckout を 1 回だけ試す。
     */
    public function testStatusFinalFallsBackToCompleteCheckout(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->method('checkoutStatus')->willReturn(['state' => 'pending', 'kind' => 'payment']);
        $mock->expects($this->once())->method('completeCheckout')->with('cs_abc');
        $this->mockService($mock);
        $this->loginAsUser();

        $this->get('/payjp/status?session_id=cs_abc&final=1');

        $this->assertResponseOk();
    }

    /**
     * 確定済みなら final=1 でも API は叩かない。
     */
    public function testStatusFinalSkipsApiWhenAlreadyConfirmed(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->method('checkoutStatus')->willReturn(['state' => 'success', 'kind' => 'payment']);
        $mock->expects($this->never())->method('completeCheckout');
        $this->mockService($mock);
        $this->loginAsUser();

        $this->get('/payjp/status?session_id=cs_abc&final=1');

        $this->assertResponseOk();
    }
}
