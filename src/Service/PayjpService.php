<?php

declare(strict_types=1);

namespace Payjp\Service;

use Cake\ORM\TableRegistry;
use Payjp\Model\Entity\PayjpCharge;
use Payjp\Model\Entity\PayjpUser;
use Point\Service\PointService;

class PayjpService
{
    private $payjpUsersTable;
    private $payjpChargesTable;
    private PointService $pointService;

    public function __construct()
    {
        $this->payjpUsersTable  = TableRegistry::getTableLocator()->get('Payjp.PayjpUsers');
        $this->payjpChargesTable = TableRegistry::getTableLocator()->get('Payjp.PayjpCharges');
        $this->pointService     = new PointService();
    }

    public function generateIdempotencyKey(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function deleteCustomer(int $userId): PayjpUser|false
    {
        $payjpUser = $this->payjpUsersTable->find()
            ->where(['user_id' => $userId])
            ->orderByDesc('id')
            ->first();

        if (!$payjpUser) {
            return false;
        }

        $payjpUser->status = 'deleted';
        if (!$this->payjpUsersTable->save($payjpUser)) {
            return false;
        }

        return $payjpUser;
    }

    public function registerCustomer(
        int $userId,
        string $type,
        string $payjpCardToken,
        ?int $autoChargeAmount = null,
        array $cardInfo = []
    ): PayjpUser|false {
        $apiResult = $this->payjpCreateCustomer($payjpCardToken);

        $data = [
            'user_id'           => $userId,
            'type'              => $type,
            'auto_charge_amount' => $autoChargeAmount,
            'payjp_card_token'  => $payjpCardToken,
            'card_brand'        => $cardInfo['card_brand'] ?? null,
            'card_last4'        => $cardInfo['card_last4'] ?? null,
        ];

        if ($apiResult === false) {
            $data['status'] = 'failure';
            $payjpUser = $this->payjpUsersTable->newEntity($data);
            $this->payjpUsersTable->save($payjpUser);
            return false;
        }

        $data['status']              = 'active';
        $data['payjp_customer_code'] = $apiResult['id'];
        $payjpUser = $this->payjpUsersTable->newEntity($data);

        if (!$this->payjpUsersTable->save($payjpUser)) {
            return false;
        }

        return $payjpUser;
    }

    public function chargeOneTime(
        int $userId,
        int $amount,
        string $payjpCardToken,
        array $cardInfo = []
    ): PayjpCharge|false {
        return $this->executeCharge('one_time', $userId, $amount, $payjpCardToken, null, $cardInfo);
    }

    public function chargeAuto(int $userId): PayjpCharge|false
    {
        $payjpUser = $this->payjpUsersTable->find()
            ->where([
                'user_id' => $userId,
                'status IN' => ['active', 'suspended'],
            ])
            ->orderByDesc('id')
            ->first();

        if (!$payjpUser) {
            return false;
        }

        $result = $this->executeCharge(
            'auto_charge',
            $userId,
            (int) $payjpUser->auto_charge_amount,
            null,
            $payjpUser->payjp_customer_code
        );

        if ($result !== false) {
            $payjpUser->status = 'active';
            $this->payjpUsersTable->save($payjpUser);
        }

        return $result;
    }

    protected function executeCharge(
        string $type,
        int $userId,
        int $amount,
        ?string $payjpCardToken,
        ?string $payjpCustomerCode,
        array $cardInfo = []
    ): PayjpCharge|false {
        $idempotencyKey = $this->generateIdempotencyKey();

        $params = ['amount' => $amount, 'currency' => 'jpy'];
        if ($payjpCardToken !== null) {
            $params['card'] = $payjpCardToken;
        }
        if ($payjpCustomerCode !== null) {
            $params['customer'] = $payjpCustomerCode;
        }

        $apiResult = $this->payjpCreateCharge($params, $idempotencyKey);

        $chargeData = [
            'user_id'        => $userId,
            'type'           => $type,
            'amount'         => $amount,
            'idempotency_key' => $idempotencyKey,
            'payjp_card_token' => $payjpCardToken,
        ];

        if ($apiResult === false) {
            $chargeData['status'] = 'failure';
            $payjpCharge = $this->payjpChargesTable->newEntity($chargeData);
            $this->payjpChargesTable->save($payjpCharge);
            return false;
        }

        $chargeData['status']            = 'success';
        $chargeData['payjp_status']      = $apiResult['status'] ?? null;
        $chargeData['payjp_charge_code'] = $apiResult['id'] ?? null;
        $chargeData['card_brand']        = $apiResult['card']['brand'] ?? ($cardInfo['card_brand'] ?? null);
        $chargeData['card_last4']        = $apiResult['card']['last4'] ?? ($cardInfo['card_last4'] ?? null);

        $pointBook = $this->pointService->charge($userId, $amount, [
            'app_name'      => 'Payjp',
            'foreign_model' => 'PayjpCharges',
        ]);

        if ($pointBook !== false) {
            $chargeData['point_book_id'] = $pointBook->id;
        }

        $payjpCharge = $this->payjpChargesTable->newEntity($chargeData);

        if (!$this->payjpChargesTable->save($payjpCharge)) {
            return false;
        }

        return $payjpCharge;
    }

    protected function payjpCreateCustomer(string $cardToken): array|false
    {
        try {
            \Payjp\Payjp::setApiKey(env('PAYJP_SECRET_KEY', ''));
            $customer = \Payjp\Customer::create(['card' => $cardToken]);
            return ['id' => $customer->id];
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function payjpCreateCharge(array $params, string $idempotencyKey): array|false
    {
        try {
            \Payjp\Payjp::setApiKey(env('PAYJP_SECRET_KEY', ''));
            $charge = \Payjp\Charge::create($params, ['idempotency_key' => $idempotencyKey]);
            return [
                'id'     => $charge->id,
                'status' => $charge->status,
                'card'   => [
                    'brand' => $charge->card->brand ?? null,
                    'last4' => $charge->card->last4 ?? null,
                ],
            ];
        } catch (\Throwable $e) {
            return false;
        }
    }
}
