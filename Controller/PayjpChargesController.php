<?php 

class PayjpChargesController extends PayjpAppController {
  
  public $name = 'PayjpCharges';

  public $uses = array('Plugin', 'Payjp.PayjpCharge', 'Payjp.PayjpCustomer', 'Members.Mypage', 'Members.Mylog');
  
  public $helpers = array('BcPage', 'BcHtml', 'BcTime', 'BcForm');
  
  public $components = ['BcAuth', 'Cookie', 'BcAuthConfigure'];
  
  public $subMenuElements = array('');

  public $crumbs = array(
    array('name' => 'マイページトップ', 'url' => array('plugin' => 'members', 'controller' => 'mypages', 'action' => 'index')),
  );

  public function beforeFilter() {
    parent::beforeFilter();
    //$this->BcAuth->allow('');
    if(preg_match('/^admin_/', $this->action)){
	   $this->subMenuElements = array('payjp');
    }
    $this->Security->unlockedActions = array('payment');
  }

	public function admin_index() {
		$this->pageTitle = 'チャージ（カード決済一覧）';
		$conditions = [];
		if ($this->request->is('post')){
			$data = $this->request->data;
			if($data['PayjpCharge']['id']) $conditions[] = array('PayjpCharge.id' => $data['PayjpCharge']['id']);
			if($data['PayjpCharge']['mypage_id']) $conditions[] = array('PayjpCharge.mypage_id' => $data['PayjpCharge']['mypage_id']);
			if($data['Mypage']['name']) $conditions[] = array('Mypage.name like' => '%'.$data['Mypage']['name'].'%');
		}
		$this->paginate = array('conditions' => $conditions,
		'order' => 'PayjpCharge.created DESC',
		'limit' => 50
		);
		//$this->PointUser->unbindModel(['hasMany' => ['PointBook']]);
		$PayjpCharge = $this->paginate('PayjpCharge');
		$this->set('PayjpCharge', $PayjpCharge);
	}
  
  
  // フロント画面用のデフォルトアクション
  public function index() {
    $user = $this->BcAuth->user();
    $this->pageTitle = 'Payjp';
  }
  
  
  // payjp 決済画面
  public function payment($amount = null){
	  $user = $this->BcAuth->user();
      if(!$user){
		$this->setMessage('エラー: user error.', true);
		$this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
	  }
	  $this->pageTitle = '決済';
	  if($this->request->data){
		$amount = $this->request->data['PayjpCharge']['amount'];
		if(empty($amount)){
		  $this->setMessage('Error:', true);
		  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
		}
		$payjp_token = $this->request->data['payjp-token'];
		if(empty($payjp_token)){
		  $this->setMessage('カード情報が入力されていません。', true);
		  $this->redirect(array('controller'=>'payjp_charges', 'action' => 'payment/'.$amount));
		}
		//$PayjpCharge = $this->PayjpCharge->onceCharge($payjp_token, $amount, $user['id'], true);
		$PayjpCharge = $this->PayjpCharge->createAndCharge($payjp_token, $amount, $user['id']);
		if($PayjpCharge){
			$this->setMessage('決済完了。ありがとうございます。');
			$this->redirect(array('controller'=>'payjp_charges', 'action' => 'thanks/'.$PayjpCharge['PayjpCharge']['id']));
		}else{
			$this->setMessage('決済エラー：時間を空けて再度お試しいただくか、お問合せよりご連絡ください。', true);
			$this->redirect(array('controller'=>'payjp_charges', 'action' => 'payment/'.$amount));
		}
	  }
	  $this->set('amount', $amount);
	  $this->set('payjp_public', Configure::read('payjp.public'));
  }
  
  public function thanks($id){
	  $this->pageTitle = 'Thanks';
	  $PayjpCharge = $this->PayjpCharge->findById($id);
	  $this->set('charge', $PayjpCharge);
  }
  
  //オートチャージの開始、設定変更、解除
  public function auto_charge(){
	  $user = $this->BcAuth->user();
	  $this->pageTitle = 'オートチャージ(自動決済)';
	  $PointUser = $this->PointUser->findByMypageId($user['id']);
	  if($PointUser['PointUser']['pay_plan'] == 'auto' && !empty($PointUser['PointUser']['payjp_card_token'])){
		  $isAutoCharge = true;
	  }else{
		  $isAutoCharge = false;
	  }
	  if($this->request->data){
		  $charge = $this->request->data['PointUser']['charge'];
		  $payjp_token = $this->request->data['payjp-token'];
		  if($isAutoCharge){
			  if(empty($charge) && empty($payjp_token)){
				  $this->setMessage('変更はありません。', true);
				  $this->redirect(array('controller'=>'point_users', 'action' => 'auto_charge'));
			  }
			  if($this->PointUser->payjpEditAutoCharge($payjp_token, $charge, $user['id'])){
				  $this->setMessage('オートチャージの設定を変更しました。');
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }else{
				  $this->setMessage('エラー：時間を空けて再度お試しいただくか、お問合せからご連絡ください。', true);
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }
		  }else{
			  if(empty($charge)){
				  $this->setMessage('金額が選択されていません。', true);
				  $this->redirect(array('controller'=>'point_users', 'action' => 'auto_charge'));
			  }
			  if(empty($payjp_token)){
				  $this->setMessage('カード情報が入力されていません。', true);
				  $this->redirect(array('controller'=>'point_users', 'action' => 'auto_charge'));
			  }
			  if($this->PointUser->payjpNewAutoCharge($payjp_token, $charge, $user['id'])){
				  $this->setMessage('オートチャージを設定しました。');
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }else{
				  $this->setMessage('エラー：時間を空けて再度お試しいただくか、お問合せからご連絡ください。', true);
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }
		  }
	  }
	  $chargeList = [];
	  $amountList = Configure::read('PointPlugin.AmountList');
	  foreach($amountList as $amount=>$point){
		  $chargeList[$amount] = number_format($amount).'円('.$point.'ポイント)';
	  }
	  $this->set('chargeList', $chargeList);
	  $this->set('BreakPoint', Configure::read('PointPlugin.BreakPoint'));
	  $this->set('payjp_public', Configure::read('payjp.public'));
	  $this->set('PointUser', $PointUser);
	  $this->set('isAutoCharge', $isAutoCharge);
  }
  
  //オートチャージ解除
  public function cancell_auto_charge(){
	  $user = $this->BcAuth->user();
	  $this->pageTitle = 'オートチャージ解除';
	  $PointUser = $this->PointUser->findByMypageId($user['id']);
	  if($PointUser['PointUser']['pay_plan'] == 'auto' && !empty($PointUser['PointUser']['payjp_card_token'])){
		  $isAutoCharge = true;
	  }else{
		  $isAutoCharge = false;
	  }
	  if(!$isAutoCharge){
		  $this->setMessage('オートチャージは登録されていません。', true);
		  $this->redirect(array('controller'=>'point_users', 'action' => 'auto_charge'));
	  }
	  if($this->request->data){
		  if($this->request->data['PointUser']['cancell'] == '1'){
			  if($this->PointUser->payjpCancellAutoCharge($user['id'])){
				  $this->setMessage('オートチャージを解除しました。');
				  $this->redirect(array('plugin' => 'members','controller'=>'mypages', 'action' => 'index'));
			  }else{
				  $this->setMessage('エラー：解除失敗。お手数ですが、お問合せからご連絡ください。', true);
				  $this->redirect(array('controller'=>'point_users', 'action' => 'cancell_auto_charge'));
			  }
		  }else{
			  $this->setMessage('チェックを入れてボタンを押してください。', true);
			  $this->redirect(array('controller'=>'point_users', 'action' => 'cancell_auto_charge'));
		  }
	  }
	  
	  $this->set('PointUser', $PointUser);
  }



}






?>