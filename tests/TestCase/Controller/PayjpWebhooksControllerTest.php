<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Controller;

use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Payjp\Controller\PayjpWebhooksController;
use Payjp\Service\PayjpService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use ReflectionProperty;
use RuntimeException;

/**
 * Payjp\Controller\PayjpWebhooksController Test Case
 *
 * 外部 API（PayjpService::handleWebhookById → PayjpApiService::getEvent）を叩かないよう、
 * Controller.initialize イベントで PayjpService をモックに差し替える。webhook は処理の成否や
 * 例外に関わらず常に 200 を返すこと、生ペイロードの id だけを handleWebhookById に渡すことを検証する。
 *
 * @uses \Payjp\Controller\PayjpWebhooksController
 */
#[AllowMockObjectsWithoutExpectations]
class PayjpWebhooksControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Controller.initialize で webhook コントローラーに mock サービスを注入する。
     */
    private function mockService(PayjpService $mock): void
    {
        EventManager::instance()->on('Controller.initialize', function (EventInterface $event) use ($mock): void {
            $controller = $event->getSubject();
            if ($controller instanceof PayjpWebhooksController) {
                $prop = new ReflectionProperty($controller, 'payjpService');
                $prop->setValue($controller, $mock);
            }
        });
    }

    public function testRejectsNonPostMethod(): void
    {
        // ルートはコントローラーに到達し、allowMethod が非 POST を 405 で返す
        // （fallback に落として MissingController エラーログを誘発しない）。
        $this->get('/payjp/webhook');
        $this->assertResponseCode(405);
    }

    public function testReturns200AndDelegatesEventId(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->expects($this->once())
            ->method('handleWebhookById')
            ->with('evnt_abc')
            ->willReturn(true);
        $this->mockService($mock);

        $this->post('/payjp/webhook', (string)json_encode(['id' => 'evnt_abc', 'type' => 'checkout_session.completed']));

        $this->assertResponseCode(200);
    }

    public function testReturns200OnEmptyBody(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->method('handleWebhookById')->with('')->willReturn(false);
        $this->mockService($mock);

        $this->post('/payjp/webhook', '');

        $this->assertResponseCode(200);
    }

    public function testReturns200OnInvalidJson(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->method('handleWebhookById')->with('')->willReturn(false);
        $this->mockService($mock);

        $this->post('/payjp/webhook', 'not-json');

        $this->assertResponseCode(200);
    }

    public function testReturns200WhenServiceThrows(): void
    {
        $mock = $this->createMock(PayjpService::class);
        $mock->method('handleWebhookById')->willThrowException(new RuntimeException('boom'));
        $this->mockService($mock);

        $this->post('/payjp/webhook', (string)json_encode(['id' => 'evnt_err']));

        $this->assertResponseCode(200);
    }
}
