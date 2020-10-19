<?php
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php');
umask(0);

Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$rewardsHelper = Mage::helper('rewards/balance');

$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
$sql = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*')->setOrder('created_at', 'desc')->getSelect();
$customersData = $connection->fetchAll($sql);
$total = array();
foreach($customersData as $row)
{
	$customer = Mage::getModel('customer/customer')->load($row['entity_id']);
	$billingAddressText = '';
	$shippingAddressText = '';
	if($billingAddress = $customer->getPrimaryBillingAddress())
	{
		$billingAddressText = trim(preg_replace('/\s+/', ' ', $billingAddress->getFormated()));
	}
	if($shippingAddress = $customer->getPrimaryShippingAddress())
	{
		$shippingAddressText = trim(preg_replace('/\s+/', ' ', $shippingAddress->getFormated()));
	}
	array_unshift($total, array($row['entity_id'], $rewardsHelper->getBalancePoints($customer), $customer->getName(), $customer->getEmail(), $billingAddressText, $shippingAddressText));
}
$file = fopen('', 'w+');
fputcsv($file, array('ID', 'Reward Points', 'Name', 'Email', 'Billing Address', 'Shipping Address'), ',', '"', '\\');
foreach($total as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo 'DONE!';
?>