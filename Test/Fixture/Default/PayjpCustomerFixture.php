<?php

class PayjpCustomerFixture extends CakeTestFixture {
	
	public $import = array('model' => 'Payjp.PayjpCustomer');
	
	
	public $records = array(
		array(
			'id' => 1,
			'mypage_id' => 1,
			'status' => 'success',
			'card_token' => 'test',
			'brand' => 'test',
			'last4' => '1234',
			'created' => '2018-07-30 16:26:01',
			'modified' => '2018-07-30 16:26:01',
		),
	);

}