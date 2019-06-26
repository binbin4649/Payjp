<?php
 
$config['BcApp.adminNavi.payjp'] = array(
  'name' => 'Payjp',
  'contents' => array(
    array('name' => 'チャージ', 'url' => array('admin' => true, 'plugin' => 'payjp', 'controller' => 'payjp_charges', 'action' => 'index')),
    array('name' => 'カスタマー', 'url' => array('admin' => true, 'plugin' => 'payjp', 'controller' => 'payjp_customers', 'action' => 'index')),
  )
);


?>