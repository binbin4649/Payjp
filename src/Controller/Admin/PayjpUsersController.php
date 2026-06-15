<?php

declare(strict_types=1);

namespace Payjp\Controller\Admin;


use Payjp\Model\Entity\PayjpUser;
use Cake\ORM\TableRegistry;
use Cake\Event\EventInterface;

/**
 * PayjpUsers Controller
 *
 * @property \Payjp\Model\Table\PayjpUsersTable $PayjpUsers
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class PayjpUsersController extends AppController
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
        $this->set('title', 'payjpUsers一覧');
        $this->Authorization->skipAuthorization();
        $keyword = $id = '';
        $query = $this->PayjpUsers->find()
            ->contain(['Users']);
        $queryParams = $this->Mem->cleaningParams($this->request->getQuery());
        extract($queryParams);
        if (!empty($keyword)) {
            $query->where([
                'OR' => [
                    'PayjpUsers.name LIKE' => '%' . $keyword . '%',
                ]
            ]);
        }
        if (!empty($id)) $query->where(['PayjpUsers.id' => $id]);
        $payjpUsers = $this->paginate($query);
        $payjpUser = $this->PayjpUsers->newEmptyEntity();
        $Identity = $this->Authentication->getIdentity();

        $this->set(compact('payjpUsers', 'payjpUser', 'Identity', 'keyword', 'id'));
    }

    /**
     * View method
     *
     * @param string|null $id Payjp User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->set('title', 'Payjp User詳細');
        $Identity = $this->Authentication->getIdentity();
        $payjpUser = $this->PayjpUsers->get($id, contain: ['Users']);
        $this->Authorization->authorize($payjpUser, 'view');
        $changeLogs = $this->changeLogTable->find('latest', model_name: 'PayjpUsers', record_id: $id);
        $this->set(compact('payjpUser', 'changeLogs', 'Identity'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->set('title', 'Payjp User追加');
        $Identity = $this->Authentication->getIdentity();
        $payjpUser = $this->PayjpUsers->newEmptyEntity();
        $this->Authorization->authorize($payjpUser, 'add');
        if ($this->request->is('post')) {
            $payjpUser = $this->PayjpUsers->patchEntity($payjpUser, $this->request->getData());
            $payjpUser->admin_id = $Identity->id; //ログ用
            if ($this->PayjpUsers->save($payjpUser)) {
                $this->Flash->success('payjp userを登録しました。');

                return $this->redirect(['action' => 'view', $payjpUser->id]);
            }
            $this->Flash->error('payjp userを登録できませんでした。下記メッセージを確認してください。');
        }
        $users = $this->PayjpUsers->Users->find('list', limit: 200)->all();
        $this->set(compact('payjpUser', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Payjp User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $Identity = $this->Authentication->getIdentity();
        $this->set('title', 'Payjp User編集');
        $payjpUser = $this->PayjpUsers->get($id, contain: []);
        $this->Authorization->authorize($payjpUser, 'edit');
        if ($this->request->is(['patch', 'post', 'put'])) {
            $payjpUser = $this->PayjpUsers->patchEntity($payjpUser, $this->request->getData());
            $payjpUser->admin_id = $Identity->id; //ログ用
            if ($this->PayjpUsers->save($payjpUser)) {
                $this->Flash->success('payjp userを編集しました。');

                return $this->redirect(['action' => 'view', $payjpUser->id]);
            }
            $this->Flash->error('payjp userを編集できませんでした。下記メッセージを確認してください。');
        }
        $users = $this->PayjpUsers->Users->find('list', limit: 200)->all();
        $this->set(compact('payjpUser', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Payjp User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $Identity = $this->Authentication->getIdentity();
        $this->request->allowMethod(['post', 'delete']);
        $payjpUser = $this->PayjpUsers->get($id);
        $this->Authorization->authorize($payjpUser, 'delete');
        $payjpUser->admin_id = $Identity->id;
        if ($this->PayjpUsers->delete($payjpUser)) {
            $this->Flash->success('payjp userを削除しました。');
        } else {
            $this->Flash->error('payjp userを削除できませんでした。下記メッセージを確認してください。');
        }

        return $this->redirect(['action' => 'index']);
    }
}
