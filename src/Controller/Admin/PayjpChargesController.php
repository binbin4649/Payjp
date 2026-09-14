<?php

declare(strict_types=1);

namespace Payjp\Controller\Admin;

use Payjp\Model\Entity\PayjpCharge;
use Cake\ORM\TableRegistry;

/**
 * PayjpCharges Controller
 *
 * @property \Payjp\Model\Table\PayjpChargesTable $PayjpCharges
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class PayjpChargesController extends AppController
{
    protected \Member\Model\Table\ChangeLogsTable $changeLogTable;

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->changeLogTable = TableRegistry::getTableLocator()->get('Member.ChangeLogs');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->set('title', 'payjpCharges一覧');
        $this->Authorization->skipAuthorization();
        $params = $this->Mem->cleaningParams($this->request->getQuery());
        $keyword = $params['keyword'] ?? '';
        $id = $params['id'] ?? '';
        $userId = $params['user_id'] ?? '';
        $query = $this->PayjpCharges->find('search', keyword: (string)$keyword, id: (string)$id, userId: (string)$userId)
            ->contain(['Users', 'PointBooks']);
        $payjpCharges = $this->paginate($query);
        $payjpCharge = $this->PayjpCharges->newEmptyEntity();
        $Identity = $this->Authentication->getIdentity();
        $this->set(compact('payjpCharges', 'payjpCharge', 'Identity', 'keyword', 'id', 'userId'));
    }

    /**
     * View method
     *
     * @param string|null $id Payjp Charge id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->set('title', 'Payjp Charge詳細');
        $Identity = $this->Authentication->getIdentity();
        $payjpCharge = $this->PayjpCharges->get($id, contain: ['Users', 'PointBooks']);
        $this->Authorization->authorize($payjpCharge, 'view');
        $changeLogs = $this->changeLogTable->find('latest', model_name: 'PayjpCharges', record_id: $id);
        $this->set(compact('payjpCharge', 'changeLogs', 'Identity'));
    }
}
