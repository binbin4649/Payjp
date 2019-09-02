<?php
App::uses('PayjpCustomer', 'Payjp.Model');

class PayjpCustomerTest extends BaserTestCase {
	
    public $fixtures;
    
    public function __construct(){
	    $fixtures = array(
	        'plugin.payjp.Default/PayjpCustomer',
	        'plugin.payjp.Default/PayjpCharge',
	        'plugin.payjp.Default/Mypage'
	    );
/*
	    $Plugin = ClassRegistry::init('Plugin');
	    $lists = $Plugin->find('list');
	    foreach($lists as $list){
		    if($list == 'Nos'){
			    $fixtures[] = 'plugin.point.Default/NosCall';
			    $fixtures[] = 'plugin.point.Default/NosUser';
		    } 
	    }
*/
	    $this->fixtures = $fixtures;
    }
    

    public function setUp() {
        $this->PayjpCustomer = ClassRegistry::init('Payjp.PayjpCustomer');
        parent::setUp();
    }
    
    public function tearDown(){
	    unset($this->PayjpCustomer);
	    parent::tearDown();
    }
    
    public function testDummy(){
	    $r = $this->PayjpCustomer->findById(1);
	    $this->assertEquals('test', $r['PayjpCustomer']['card_token']);
    }
    
/*
    public function testFalsePointAdd(){
	    // mypage_idが無い
	    $data = ['mypage_id'=>'', 'point'=>'100', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->pointAdd($data));
	    
	    // pointが無い
	    $data = ['mypage_id'=>1, 'point'=>'', 'reason'=>'test'];
	    $this->assertFalse($this->PointUser->pointAdd($data));
	    
	    // reasonが無い
	    $data = ['mypage_id'=>1, 'point'=>'100', 'reason'=>''];
	    $this->assertFalse($this->PointUser->pointAdd($data));
    }
    
    public function testTruePointAdd(){
	    $data = ['mypage_id'=>1, 'point'=>'100', 'reason'=>'test'];
	    $r = $this->PointUser->pointAdd($data);
	    $this->assertEquals(200, $r['PointBook']['point_balance']);
	    $this->assertEquals('test', $r['PointBook']['reason']);
    }
*/
    
    

}