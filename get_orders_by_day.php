<?php
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '3072M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$fromDate = '2020-08-05 00:00:00';
$toDate = '2020-08-05 23:59:59';

$orders = Mage::getModel('sales/order')->getCollection()
		->addAttributeToSelect('*')
		->addAttributeToFilter('created_at', array('gteq'=>$fromDate))
		->addAttributeToFilter('created_at', array('lteq'=>$toDate));

$guests = 0;
$registered = 0;	
foreach($orders as $order)
{
	if($order->getCustomerIsGuest()) $guests++;
	else $registered++;
}

echo 'From ' . $fromDate . ' to ' . $toDate . '<br>';
echo 'Guests: ' . $guests . '<br>';
echo 'Registered: ' . $registered . '<br>';
?>