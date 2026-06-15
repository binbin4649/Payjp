<?php

declare(strict_types=1);

namespace Payjp\Controller\Admin;


use Payjp\Model\Entity\PayjpCharge;
use Cake\ORM\TableRegistry;
use Cake\Event\EventInterface;

/**
 * PayjpCharges Controller
 *
 * @property \Payjp\Model\Table\PayjpChargesTable $PayjpCharges
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class PayjpChargesController extends AppController
{
    protected $changeLogTable;

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->changeLogTable = TableRegistry::getTableLocator()->get('Member.ChangeLogs');
        //$this->Authentication->allowUnauthenticated(['login']);
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
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
        $keyword = $id = '';
        $query = $this->PayjpCharges->find()
            ->contain(['Users', 'PointBooks']);
        $queryParams = $this->Mem->cleaningParams($this->request->getQuery());
        extract($queryParams);
        if (!empty($keyword)) {
            $query->where([
                'OR' => [
                    'PayjpCharges.name LIKE' => '%' . $keyword . '%',
                ]
            ]);
        }
        if (!empty($id)) $query->where(['PayjpCharges.id' => $id]);
        $payjpCharges = $this->paginate($query);
        $payjpCharge = $this->PayjpCharges->newEmptyEntity();
        $Identity = $this->Authentication->getIdentity();

        $this->set(compact('payjpCharges', 'payjpCharge', 'Identity', 'keyword', 'id'));
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

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->set('title', 'Payjp Charge追加');
        $Identity = $this->Authentication->getIdentity();
        $payjpCharge = $this->PayjpCharges->newEmptyEntity();
        $this->Authorization->authorize($payjpCharge, 'add');
        if ($this->request->is('post')) {
            $payjpCharge = $this->PayjpCharges->patchEntity($payjpCharge, $this->request->getData());
            $payjpCharge->admin_id = $Identity->id; //ログ用
            if ($this->PayjpCharges->save($payjpCharge)) {
                $this->Flash->success('payjp chargeを登録しました。');

                return $this->redirect(['action' => 'view', $payjpCharge->id]);
            }
            $this->Flash->error('payjp chargeを登録できませんでした。下記メッセージを確認してください。');
        }
        $users = $this->PayjpCharges->Users->find('list', limit: 200)->all();
        $pointBooks = $this->PayjpCharges->PointBooks->find('list', limit: 200)->all();
        $this->set(compact('payjpCharge', 'users', 'pointBooks'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Payjp Charge id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $Identity = $this->Authentication->getIdentity();
        $this->set('title', 'Payjp Charge編集');
        $payjpCharge = $this->PayjpCharges->get($id, contain: []);
        $this->Authorization->authorize($payjpCharge, 'edit');
        if ($this->request->is(['patch', 'post', 'put'])) {
            $payjpCharge = $this->PayjpCharges->patchEntity($payjpCharge, $this->request->getData());
            $payjpCharge->admin_id = $Identity->id; //ログ用
            if ($this->PayjpCharges->save($payjpCharge)) {
                $this->Flash->success('payjp chargeを編集しました。');

                return $this->redirect(['action' => 'view', $payjpCharge->id]);
            }
            $this->Flash->error('payjp chargeを編集できませんでした。下記メッセージを確認してください。');
        }
        $users = $this->PayjpCharges->Users->find('list', limit: 200)->all();
        $pointBooks = $this->PayjpCharges->PointBooks->find('list', limit: 200)->all();
        $this->set(compact('payjpCharge', 'users', 'pointBooks'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Payjp Charge id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $Identity = $this->Authentication->getIdentity();
        $this->request->allowMethod(['post', 'delete']);
        $payjpCharge = $this->PayjpCharges->get($id);
        $this->Authorization->authorize($payjpCharge, 'delete');
        $payjpCharge->admin_id = $Identity->id;
        if ($this->PayjpCharges->delete($payjpCharge)) {
            $this->Flash->success('payjp chargeを削除しました。');
        } else {
            $this->Flash->error('payjp chargeを削除できませんでした。下記メッセージを確認してください。');
        }

        return $this->redirect(['action' => 'index']);
    }
}
