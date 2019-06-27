<?php 

class PayjpCustomersController extends PayjpAppController {
  
  public $name = 'PayjpCustomers';

  public $uses = array('Plugin', 'Payjp.PayjpCustomer', 'Payjp.PayjpCharge', 'Members.Mypage');
  
  public $helpers = array('BcPage', 'BcHtml', 'BcTime', 'BcForm');
  
  public $components = ['BcAuth', 'Cookie', 'BcAuthConfigure'];
  
  public $subMenuElements = array('');

  public $crumbs = array(
    array('name' => 'マイページトップ', 'url' => array('plugin' => 'members', 'controller' => 'mypages', 'action' => 'index')),
  );

  public function beforeFilter() {
    parent::beforeFilter();
	if(preg_match('/^admin_/', $this->action)){
	   $this->subMenuElements = array('payjp');
    }
    //$this->BcAuth->allow('');
  }

	public function admin_index() {
		$this->pageTitle = 'カスタマー（登録カード一覧）';
		$conditions = [];
		if ($this->request->is('post')){
			$data = $this->request->data;
			if($data['PayjpCustomer']['mypage_id']) $conditions[] = array('PayjpCustomer.mypage_id' => $data['PayjpCustomer']['mypage_id']);
			if($data['Mypage']['name']) $conditions[] = array('Mypage.name like' => '%'.$data['Mypage']['name'].'%');
		}
		$this->paginate = array('conditions' => $conditions,
		'order' => 'PayjpCustomer.modified DESC',
		'limit' => 50
		);
		$this->PayjpCustomer->unbindModel(['hasMany' => ['PayjpCharge']]);
		$PayjpCustomer = $this->paginate('PayjpCustomer');
		$this->set('PayjpCustomer', $PayjpCustomer);
	}





}






?>