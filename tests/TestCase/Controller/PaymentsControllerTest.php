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
}
