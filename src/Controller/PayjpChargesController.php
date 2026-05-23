<?php
declare(strict_types=1);

namespace Payjp\Controller;

use App\Controller\AppController;

/**
 * PayjpCharges Controller
 *
 * @property \Payjp\Model\Table\PayjpChargesTable $PayjpCharges
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class PayjpChargesController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Authorization.Authorization');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->PayjpCharges->find()
            ->contain(['Users', 'PointBooks']);
        $query = $this->Authorization->applyScope($query);
        $payjpCharges = $this->paginate($query);

        $this->set(compact('payjpCharges'));
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
        $payjpCharge = $this->PayjpCharges->get($id, contain: ['Users', 'PointBooks']);
        $this->Authorization->authorize($payjpCharge);
        $this->set(compact('payjpCharge'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $payjpCharge = $this->PayjpCharges->newEmptyEntity();
        $this->Authorization->authorize($payjpCharge);
        if ($this->request->is('post')) {
            $payjpCharge = $this->PayjpCharges->patchEntity($payjpCharge, $this->request->getData());
            if ($this->PayjpCharges->save($payjpCharge)) {
                $this->Flash->success(__('The payjp charge has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The payjp charge could not be saved. Please, try again.'));
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
        $payjpCharge = $this->PayjpCharges->get($id, contain: []);
        $this->Authorization->authorize($payjpCharge);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $payjpCharge = $this->PayjpCharges->patchEntity($payjpCharge, $this->request->getData());
            if ($this->PayjpCharges->save($payjpCharge)) {
                $this->Flash->success(__('The payjp charge has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The payjp charge could not be saved. Please, try again.'));
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
        $this->request->allowMethod(['post', 'delete']);
        $payjpCharge = $this->PayjpCharges->get($id);
        $this->Authorization->authorize($payjpCharge);
        if ($this->PayjpCharges->delete($payjpCharge)) {
            $this->Flash->success(__('The payjp charge has been deleted.'));
        } else {
            $this->Flash->error(__('The payjp charge could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
