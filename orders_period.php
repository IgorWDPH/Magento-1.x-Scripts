<?php
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '3072M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
echo 'Start<br>';
$total = array();
$total[] = array('Increment ID', 'email', 'Name(Shipping Address)');
$startingId = 100052560;
$endingId = 100052961;
$orders = Mage::getModel('sales/order')->getCollection()->addFieldToSelect('*');
foreach($orders as $order)
{
	if(intval($order->getIncrementId()) < $startingId || intval($order->getIncrementId()) > $endingId)
	{
		continue;
	}
	$total[] = array($order->getIncrementId(), $order->getCustomerEmail(), $order->getShippingAddress()->getName());
}
$file = fopen('123.csv', 'w+');
foreach($total as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo 'DONE!!!';