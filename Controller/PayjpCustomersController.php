<?php 

class PayjpCustomersController extends PointAppController {
  
  public $name = 'PayjpCustomers';

  public $uses = array('Plugin', 'Point.PointUser', 'Point.PointBook', 'Members.Mypage');
  
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

  //管理画面用のデフォルトアクション
  public function admin_index() {
    $this->pageTitle = 'PointBook';
    $conditions = [];
    if ($this->request->is('post')){
      $data = $this->request->data;
      if($data['PointBook']['mypage_id']) $conditions[] = array('PointBook.mypage_id' => $data['PointBook']['mypage_id']);
      if($data['PointBook']['reason']) $conditions[] = array('PointBook.reason' => $data['PointBook']['reason']);
    }
    $this->paginate = array('conditions' => $conditions,
      'order' => 'PointBook.id DESC',
      'limit' => 50
    );
    $PointBooks = $this->paginate('PointBook');
    $this->set('PointBooks', $PointBooks);
  }
  
  





}






?>