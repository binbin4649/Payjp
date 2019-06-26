<?php
App::import('Model', 'AppModel');

class PayjpCustomer extends AppModel {

	public $name = 'PayjpCustomer';
	
	public $belongsTo = [
		'Mypage' => [
			'className' => 'Members.Mypage',
			'foreignKey' => 'mypage_id']
	];
	
    public $hasMany = [
		'PayjpCharge' => [
			'className' => 'Payjp.PayjpCharge',
			'foreignKey' => 'payjp_customer_id',
			'order' => 'PayjpCharge.created DESC',
			'limit' => 50
	]];
	
	
	
	
	
	

}
