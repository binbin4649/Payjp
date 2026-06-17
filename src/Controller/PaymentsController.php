<?php
declare(strict_types=1);

namespace Payjp\Controller;

use Cake\Http\Response;
use Cake\Log\Log;
use Payjp\Service\PayjpService;
use Throwable;

/**
 * Checkout の success_url 到達時に決済を確定する補助コントローラー。
 *
 * webhook が確定の正本だが、ユーザーがリダイレクトで戻った時点でも completeCheckout() により
 * 確定を試みる（冪等。webhook と二重確定しない）。確定後、安全な相対パスの redirect クエリが
 * あればそこへ遷移し、無ければ完了テンプレートを表示する。
 */
class PaymentsController extends AppController
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
        $this->Authentication->allowUnauthenticated(['complete']);
        $this->Authorization->skipAuthorization();
        if ($this->components()->has('FormProtection')) {
            $this->components()->unload('FormProtection');
        }
    }

    /**
     * success_url 確定エンドポイント。
     */
    public function complete(): ?Response
    {
        $this->request->allowMethod(['get']);

        $sessionId = (string)$this->request->getQuery('session_id', '');
        $entity = false;

        if ($sessionId !== '') {
            try {
                $entity = $this->payjpService()->completeCheckout($sessionId);
            } catch (Throwable $e) {
                Log::error('PaymentsController::complete error session=' . $sessionId . ': ' . $e->getMessage());
            }
        }

        $redirect = (string)$this->request->getQuery('redirect', '');
        if ($this->isSafeRelativePath($redirect)) {
            return $this->redirect($redirect);
        }

        $this->set('completed', $entity !== false);

        return null;
    }

    /**
     * PayjpService を生成する。テストでのモック差し替え用シーム。
     */
    protected function payjpService(): PayjpService
    {
        return $this->payjpService ??= new PayjpService();
    }

    /**
     * オープンリダイレクト防止のため、アプリ内の相対パスのみ許可する。
     */
    private function isSafeRelativePath(string $path): bool
    {
        return $path !== '' && str_starts_with($path, '/') && !str_starts_with($path, '//');
    }
}
