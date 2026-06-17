<?php
declare(strict_types=1);

namespace Payjp\Controller;

use Cake\Http\Response;
use Cake\Log\Log;
use Payjp\Service\PayjpService;
use Throwable;

/**
 * PAY.JP からの webhook を受信し PayjpService へ委譲して決済を確定するコントローラー。
 *
 * 認証・認可・CSRF（FormProtection）の対象外。生ペイロードは event id だけを信用し、
 * PayjpService::handleWebhookById() が PAY.JP から正本を再取得して確定する。再送ループを
 * 避けるため、処理の成否や例外に関わらず常に 200 を返す。
 */
class PayjpWebhooksController extends AppController
{
    /**
     * @var \Payjp\Service\PayjpService|null テストでの差し替え用シーム。
     */
    protected ?PayjpService $payjpService = null;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['index']);
        $this->Authorization->skipAuthorization();
        if ($this->components()->has('FormProtection')) {
            $this->components()->unload('FormProtection');
        }
    }

    /**
     * webhook 受信エンドポイント。
     */
    public function index(): Response
    {
        $this->request->allowMethod(['post']);

        $payload = json_decode((string)$this->request->getBody(), true) ?? [];
        $eventId = (string)($payload['id'] ?? '');

        try {
            $this->payjpService()->handleWebhookById($eventId);
        } catch (Throwable $e) {
            Log::error('PayjpWebhooksController::index error event=' . $eventId . ': ' . $e->getMessage());
        }

        return $this->response->withStatus(200)->withStringBody('');
    }

    /**
     * PayjpService を生成する。テストでのモック差し替え用シーム。
     */
    protected function payjpService(): PayjpService
    {
        return $this->payjpService ??= new PayjpService();
    }
}
