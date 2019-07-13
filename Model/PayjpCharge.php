<?php
require_once(dirname(__FILE__)."/../vendor/autoload.php");
App::import('Model', 'AppModel');
App::import('Model', 'Plugin');
App::import('Model', 'Members.Mylog');

class PayjpCharge extends AppModel {

	public $name = 'PayjpCharge';
	
	public $belongsTo = [
		'Mypage' => [
			'className' => 'Members.Mypage',
			'foreignKey' => 'mypage_id'],
		'PayjpCustomer' => [
			'className' => 'Payjp.PayjpCustomer',
			'foreignKey' => 'payjp_customer_id']
	];
	
    
    //有効なカード登録があるか？　あればtrue
    public function isCustomer($mypage_id){
	    $PayjpCustomer = $this->PayjpCustomer->find('first', array(
        	'conditions' => array(
        		'PayjpCustomer.mypage_id' => $mypage_id,
        		'PayjpCustomer.status' => 'success',
        	),
        	'recursive' => -1
		));
		return $PayjpCustomer;
    }
    
    
    //都度払い、customer登録しない
	public function onceCharge($payjp_token, $amount, $mypage_id, $send_mail = true){
		$amount = abs($amount);
		if($amount < 1){
			return false;
		}
		$siteUrl = Configure::read('BcEnv.siteUrl');
		$secret_key = Configure::read('payjp.secret');
		if(!Configure::read('MccPlugin.TEST_MODE')){
			\Payjp\Payjp::setApiKey($secret_key);
			try {
				$charge = \Payjp\Charge::create([
				  'card' => $payjp_token,
				  'amount'=> $amount,
				  'currency' => 'jpy'
				]);
				if (isset($charge['error'])) {
			        throw new Exception();
			    }
			}catch (Exception $e){
				$error_body = $e->getJsonBody();
				$this->log('PayjpCharge.php onceCharge : '.$error_body['error']['message']);
				return false;
			}
		}else{
			//テスト用
			$charge = (object)[
				'id' => 'test',
				'amount' => $amount,
				'card' => (object)[
					'brand' => 'test',
					'last4' => '1234'
				]
			];
		}
		// $charge->paid 与信が通った場合にtrue。都度決済で支払いと与信を同時に行うのでここではスルー。
		// $charge->captured 与信だけの場合はfalseが帰ってくる。ポイントは毎回決済するのでここではスルー。
		// $charge->id;//一意の決済key
		//念のため決済金額を確認。もし違っていたらログに残す。
		if($amount != $charge->amount){
			$this->log('Warning : PayjpCharge.php onceCharge. Different amounts. payjp_id:'.$charge->id);
		}
		// customer があれば id 加える
		$PayjpCustomer = $this->PayjpCustomer->findByMypageId($mypage_id, null, null, -1);
		if($PayjpCustomer){
			$payjp_customer_id = $PayjpCustomer['PayjpCustomer']['id'];
		}else{
			$payjp_customer_id = '';
		}
		$PayjpCharge['PayjpCharge'] = [
			'mypage_id' => $mypage_id,
			'payjp_customer_id' => $payjp_customer_id,
			'status' => 'success',
			'charge' => $amount,
			'token' => $charge->id,
			'brand' => $charge->card->brand,
			'last4' => $charge->card->last4,
		];
		$PayjpCharge = $this->save($PayjpCharge);
		if($PayjpCharge){
			$Mypage = $this->Mypage->findById($mypage_id);
			$PayjpCharge['siteUrl'] = $siteUrl;
			$PayjpCharge['loginUrl'] = $siteUrl.'members/mypages/login';
			$PayjpCharge['Mypage'] = $Mypage['Mypage'];
			if($send_mail){
				$this->Mypage->sendEmail($Mypage['Mypage']['email'], 'カード決済', $PayjpCharge, $options = ['template'=>'Payjp.once_charge']);
			}
			return $PayjpCharge;
		}else{
			$this->log('Warning : PayjpCharge.php onceCharge. save error.:'.print_r($PayjpCharge, true));
			return false;
		}
	}
	
	//顧客登録&更新
	public function createCustomer($payjp_token, $mypage_id){
		//既に登録があった場合は一旦解除してもらってから
		$PayjpCustomer = $this->PayjpCustomer->findByMypageId($mypage_id);
		if($PayjpCustomer){
			return false;
		}
		$secret_key = Configure::read('payjp.secret');
		\Payjp\Payjp::setApiKey($secret_key);
		try {
			$Customer = \Payjp\Customer::create([
			  'card' => $payjp_token,
			  'id'=> $mypage_id,
			]);
			if (isset($Customer['error'])) {
		        throw new Exception();
		    }
		    $PayjpCustomer['PayjpCustomer']['status'] = 'success';
		}catch (Exception $e){
			if(!Configure::read('MccPlugin.TEST_MODE')){
				$error_body = $e->getJsonBody();
				$this->log('PayjpCharge.php createCustomer : '.$error_body['error']['message']);
			}
			$PayjpCustomer['PayjpCustomer']['status'] = 'error';
		}
		if(!empty($Customer)){
			foreach($Customer->cards->data as $data){
				if($Customer->default_card == $data->id){
					$PayjpCustomer['PayjpCustomer']['card_token'] = $data->id;
					$PayjpCustomer['PayjpCustomer']['brand'] = $data->brand;
					$PayjpCustomer['PayjpCustomer']['last4'] = $data->last4;
				}
			}
		}
		if(empty($PayjpCustomer['PayjpCustomer']['card_token'])){
			return false;
		}else{
			$PayjpCustomer['PayjpCustomer']['mypage_id'] = $mypage_id;
			return $this->PayjpCustomer->save($PayjpCustomer);
		}
	}
	
	public function cancelCustomer($mypage_id){
		$PayjpCustomer = $this->PayjpCustomer->findByMypageId($mypage_id);
		if($PayjpCustomer){
			$secret_key = Configure::read('payjp.secret');
			\Payjp\Payjp::setApiKey($secret_key);
			try {
				$cu = \Payjp\Customer::retrieve($mypage_id);
				$cu->delete();
				if (isset($cu['error'])) {
			        throw new Exception();
			    }
			}catch (Exception $e){
				$error_body = $e->getJsonBody();
				$this->log('PayjpCharge.php cancelCustomer : '.$error_body['error']['message']);
				return false;
			}
			$this->PayjpCustomer->delete($PayjpCustomer['PayjpCustomer']['id']);
			return true;
		}else{
			return false;
		}
	}
	
	//登録してある顧客情報から支払
	public function moreThanCharge($mypage_id, $amount){
		$PayjpCustomer = $this->PayjpCustomer->findByMypageId($mypage_id);
		if(!$PayjpCustomer){
			return false;
		}
		$amount = abs($amount);
		if($amount < 1){
			return false;
		}
		$secret_key = Configure::read('payjp.secret');
		\Payjp\Payjp::setApiKey($secret_key);
		try{
			$charge = \Payjp\Charge::create([
			  'card' => $PayjpCustomer['PayjpCustomer']['card_token'],
			  'amount'=> $amount,
			  'customer' => $mypage_id,
			  'currency' => 'jpy'
			]);
			if (isset($charge['error'])) {
		        throw new Exception();
		    }
		}catch (Exception $e){
			if(!Configure::read('MccPlugin.TEST_MODE')){
				$error_body = $e->getJsonBody();
				$this->log('PayjpCharge.php moreThanCharge : '.$error_body['error']['message']);
			}
			$PayjpCustomer['PayjpCustomer']['status'] = 'error';
			$this->PayjpCustomer->create();
			$this->PayjpCustomer->save($PayjpCustomer);
			return false;
		}
		$PayjpCharge['PayjpCharge'] = [
			'mypage_id' => $mypage_id,
			'payjp_customer_id' => $PayjpCustomer['PayjpCustomer']['id'],
			'status' => 'success',
			'charge' => $amount,
			'token' => $charge->id,
			'brand' => $charge->card->brand,
			'last4' => $charge->card->last4,
		];
		$PayjpCharge = $this->save($PayjpCharge);
		if($PayjpCharge){
			$PayjpCharge['PayjpCharge']['id'] = $this->getLastInsertId();
		}
		return $PayjpCharge;
	}
	
	//顧客登録と一緒に支払いも
	public function createAndCharge($payjp_token, $amount, $mypage_id){
		$PayjpCustomer = $this->PayjpCustomer->findByMypageId($mypage_id, null, null, -1);
		//カスタマー登録がなかったら登録してから
		if(!$PayjpCustomer){
			if($this->createCustomer($payjp_token, $mypage_id)){
				return $this->moreThanCharge($mypage_id, $amount);
			}
		}else{
			return $this->moreThanCharge($mypage_id, $amount);
		}
	}
	
	
	
	
	//注：直してない
	//オートチャージ　新規登録
	public function payjpNewAutoCharge($payjp_token, $charge, $mypage_id){
		$this->Mylog = new Mylog;
		$pointUser = $this->findByMypageId($mypage_id, null, null, -1);
		$secret_key = Configure::read('payjp.secret');
		\Payjp\Payjp::setApiKey($secret_key);
		try {
			$Customer = \Payjp\Customer::create([
			  'card' => $payjp_token,
			  'id'=> $mypage_id,
			]);
			if (isset($Customer['error'])) {
		        throw new Exception();
		    }
		}catch (Exception $e){
			$error_body = $e->getJsonBody();
			$this->log('Pointuser.php payjpNewAutoCharge : '.$error_body['error']['message']);
			return false;
		}
		foreach($Customer->cards->data as $data){
			if($Customer->default_card == $data->id){
				$pointUser['PointUser']['payjp_card_token'] = $data->id;
				$pointUser['PointUser']['payjp_brand'] = $data->brand;
				$pointUser['PointUser']['payjp_last4'] = $data->last4;
			}
		}
		$pointUser['PointUser']['charge_point'] = $charge;
		$pointUser['PointUser']['pay_plan'] = 'auto';
		$pointUser['PointUser']['auto_charge_status'] = 'success';
		$pointUser['PointUser']['credit'] = 0;
		$pointUser['PointUser']['available_point'] = $pointUser['PointUser']['point'];
		if($this->save($pointUser)){
			$this->Mylog->record($mypage_id, 'autocharge_setup');
			return $this->payjpRunAutoCharge($mypage_id);
		}else{
			$this->log('Pointuser.php payjpNewAutoCharge save error: '.print_r($pointUser, true));
			return false;
		}
	}
	
	//注：直してない
	//オートチャージ　変更
	public function payjpEditAutoCharge($payjp_token, $charge, $mypage_id){
		$this->Mylog = new Mylog;
		$pointUser = $this->findByMypageId($mypage_id, null, null, -1);
		//2重クリック防止 8秒以内の更新は無効
		if((time() - strtotime($pointUser['PointUser']['modified'])) < 8) return false;
		if(!empty($charge)){
			$pointUser['PointUser']['charge_point'] = $charge;
		}
		if(!empty($payjp_token)){
			$secret_key = Configure::read('payjp.secret');
			\Payjp\Payjp::setApiKey($secret_key);
			$cu = \Payjp\Customer::retrieve($mypage_id);
			try {
				$card = $cu->cards->retrieve($pointUser['PointUser']['payjp_card_token']);
				$card->delete();
				if (isset($card['error'])) {
			        throw new Exception();
			    }
				$card = $cu->cards->create(array(
					"card" => $payjp_token,
					"default" => true
				));
				if (isset($card['error'])) {
			        throw new Exception();
			    }
			}catch (Exception $e){
				$error_body = $e->getJsonBody();
				$this->log('Pointuser.php payjpEditAutoCharge new card add error: '.$error_body['error']['message']);
				return false;
			}
			$pointUser['PointUser']['payjp_card_token'] = $card->id;
			$pointUser['PointUser']['payjp_brand'] = $card->brand;
			$pointUser['PointUser']['payjp_last4'] = $card->last4;
			$pointUser['PointUser']['auto_charge_status'] = 'success';
		}
		if($this->save($pointUser)){
			$this->Mylog->record($mypage_id, 'autocharge_edit');
			return $this->payjpRunAutoCharge($mypage_id);
		}else{
			$this->log('Pointuser.php payjpEditAutoCharge save error: '.print_r($pointUser, true));
			return false;
		}
	}
	
	//注：直してない
	//オートチャージ実行。現ポイントを見てbreakPoint以下だったら実行。
	public function payjpRunAutoCharge($mypage_id){
		$pointUser = $this->findByMypageId($mypage_id);
		$BreakPoint = Configure::read('PointPlugin.BreakPoint');
		$siteUrl = Configure::read('BcEnv.siteUrl');
		$amountList = Configure::read('PointPlugin.AmountList');
		if(!empty($pointUser['PointUser']['payjp_card_token']) && 
				$pointUser['PointUser']['pay_plan'] == 'auto' &&
				$pointUser['PointUser']['point'] <= $BreakPoint &&
				$pointUser['Mypage']['status'] == 0)
			{
			$secret_key = Configure::read('payjp.secret');
			\Payjp\Payjp::setApiKey($secret_key);
			try {
				$charge = \Payjp\Charge::create([
				  'card' => $pointUser['PointUser']['payjp_card_token'],
				  'amount'=> $pointUser['PointUser']['charge_point'],
				  'customer' => $mypage_id,
				  'currency' => 'jpy'
				]);
				if (isset($charge['error'])) {
			        throw new Exception();
			    }
			}catch (Exception $e){
				$error_body = $e->getJsonBody();
				$this->log('Pointuser.php payjpRunAutoCharge : '.$error_body['error']['message']);
				if($error_body['error']['type'] == 'server_error'){
					$this->sendEmail(Configure::read('BcSite.email'), 'オートチャージ server_error', $pointUser, array('template'=>'Point.auto_charge_fail', 'layout'=>'default'));
				}elseif($error_body['error']['type'] == 'client_error'){
					$this->sendEmail(Configure::read('BcSite.email'), 'オートチャージ client_error', $pointUser, array('template'=>'Point.auto_charge_fail', 'layout'=>'default'));
				}else{
					$pointUser['PointUser']['auto_charge_status'] = 'fail';
					$this->create();
					$this->save($pointUser);
					$pointUser['PointBook']['BreakPoint'] = $BreakPoint;
					$this->sendEmail($pointUser['Mypage']['email'], 'ポイントチャージに失敗しました。', $pointUser, array('template'=>'Point.auto_charge_fail', 'layout'=>'default'));
				}
				return false;
			}
			$point_add = [
				'mypage_id' => $mypage_id,
				'point' => $amountList[$pointUser['PointUser']['charge_point']],
				'reason' => 'payjp_auto',
				'pay_token' => $charge->id,
				'charge' => $pointUser['PointUser']['charge_point']
			];
			$pointBook = $this->pointAdd($point_add);
			if($pointBook){
				$pointBook['PointBook']['BreakPoint'] = $BreakPoint;
				$pointBook['PointBook']['brand'] = $charge->card->brand;//カードのブランド
				$pointBook['PointBook']['last4'] = $charge->card->last4;//カードの下4桁
				$pointBook['PointBook']['siteUrl'] = $siteUrl;
				$pointBook['PointBook']['loginUrl'] = $siteUrl.'members/mypages/login';
				$this->sendEmail($pointUser['Mypage']['email'], 'ポイントチャージ', $pointBook, array('template'=>'Point.auto_charge', 'layout'=>'default'));
			}else{
				$this->log('Warning : PointUser.php payjpRunAutoCharge. pointAdd error. payjp_id:'.$charge->id);
				return false;
			}
		}
		return true;
	}
	
	//注：直してない
	//オートチャージ解除
	//サービス予約の解除が必要。auto_charge_status:cancell をイベントでキャッチして解除する。
	public function payjpCancellAutoCharge($mypage_id){
		$this->Mylog = new Mylog;
		$pointUser = $this->findByMypageId($mypage_id, null, null, -1);
		$secret_key = Configure::read('payjp.secret');
		\Payjp\Payjp::setApiKey($secret_key);
		try {
			$cu = \Payjp\Customer::retrieve($mypage_id);
			$cu->delete();
			if (isset($cu['error'])) {
		        throw new Exception();
		    }
		}catch (Exception $e){
			$error_body = $e->getJsonBody();
			$this->log('Pointuser.php payjpCancellAutoCharge : '.$error_body['error']['message']);
			return false;
		}
		$pointUser['PointUser']['payjp_card_token'] = NULL;
		$pointUser['PointUser']['payjp_brand'] = NULL;
		$pointUser['PointUser']['payjp_last4'] = NULL;
		$pointUser['PointUser']['charge_point'] = NULL;
		$pointUser['PointUser']['pay_plan'] = 'basic';
		$pointUser['PointUser']['auto_charge_status'] = 'cancell';
		if($this->save($pointUser)){
			$this->Mylog->record($mypage_id, 'autocharge_cancell');
			return true;
		}else{
			$this->log('Pointuser.php payjpCancellAutoCharge : '.$error_body['error']['message']);
			return false;
		}
	}
	

}
