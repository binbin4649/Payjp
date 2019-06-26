<?php
App::uses('PayjpCharge', 'Payjp.Model');

class PayjpChargeTest extends BaserTestCase {
    public $fixtures = array(
        'plugin.payjp.Default/PayjpCharge',
        'plugin.payjp.Default/PayjpCustomer',
        'plugin.payjp.Default/Mypage',
        'plugin.payjp.Default/Mylog'
    );

    public function setUp() {
        $this->PayjpCharge = ClassRegistry::init('Payjp.PayjpCharge');
        $this->PayjpCustomer = ClassRegistry::init('Payjp.PayjpCustomer');
        Configure::write('MccPlugin.TEST_MODE', true);
        //Configure::write('payjp.secret', '');//テスト用
		//Configure::write('payjp.public', '');//テスト用
		
        parent::setUp();
    }
    
    public function tearDown(){
	    unset($this->PayjpCharge);
	    parent::tearDown();
    }
	
/*
	public function testMonthlyReasonIdBook(){
		$ym = date('Ym');
		$mypage_ids[] = '40';
		$r = $this->PointBook->monthlyUserBook($ym, $mypage_ids);
		$this->assertEquals('-100', $r[0]['PointBook']['point']);
	}
*/
	public function testOnceCharge(){
		$payjp_token = 'test';
		$amount = 3500;
		$mypage_id = '1';
		$r = $this->PayjpCharge->onceCharge($payjp_token, $amount, $mypage_id, false);
		$this->assertEquals('1234', $r['PayjpCharge']['last4']);
	}
	
	public function testCreateCustomerカスタマー更新エラー(){
		$payjp_token = 'test';
		$mypage_id = '1';
		$r = $this->PayjpCharge->createCustomer($payjp_token, $mypage_id);
		$this->assertEquals('error', $r['PayjpCustomer']['status']);
	}
	
	public function testCreateCustomer登録なしで更新もエラー(){
		$payjp_token = 'test';
		$mypage_id = '99';
		$r = $this->PayjpCharge->createCustomer($payjp_token, $mypage_id);
		$this->assertFalse($r);
	}
	
	public function testMoreThanCharge(){
		$mypage_id = '1';
		$amount = '3000';
		$r = $this->PayjpCharge->moreThanCharge($mypage_id, $amount);
		$this->assertFalse($r);
		$r = $this->PayjpCustomer->findByMypageId($mypage_id);
		$this->assertEquals('error', $r['PayjpCustomer']['status']);
	}
	

}