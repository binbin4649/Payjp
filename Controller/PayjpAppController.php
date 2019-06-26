<?php
/*
* Payjpプラグイン
* 基底コントローラー
*/

/**
* Include files
*/
App::uses('AppController', 'Controller');

class PayjpAppController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		
		//ログイン画面：デフォルトだとUserControllerになってしまうので強制的に変更する。
		//$this->BcAuth->loginAction = array('plugin' => 'members', 'controller' => 'mypages', 'action' => 'login');
		//$this->BcAuth->loginRedirect = array('plugin' => 'members', 'controller' => 'mypages', 'action' => 'index');
		
		//$this->set('user', $this->BcAuth->user());
		
	}
}
