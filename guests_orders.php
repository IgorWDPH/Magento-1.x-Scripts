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
$total[] = array('GUESTS', '', '', '', '');
$total[] = array('', '', '', '', '');
$total[] = array('Email', 'Name(from billing address)', 'First Order Date', 'Last Order Date', 'Orders');
$registeredCustomers = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('firstname')->addAttributeToSelect('lastname');
$registeredCustomersData = array();
foreach($registeredCustomers as $customer)
{
	$registeredCustomersData[$customer->getEmail()]['name'] = $customer->getName();
	$registeredCustomersData[$customer->getEmail()]['created_at'] = $customer->getCreatedAt();
	$registeredCustomersData[$customer->getEmail()]['updated_at'] = $customer->getUpdatedAt();
}
$orders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_is_guest', 1)->addFieldToSelect('*');
$guestCustomers = array();
echo count($orders) . ' orders found<br>';
foreach($orders as $order)
{
	if(array_key_exists($order->getCustomerEmail(), $registeredCustomersData)) continue;
	$name = $order->getBillingAddress()->getName();
	$guestCustomers[$order->getCustomerEmail()]['orders'] .= ' ' . $order->getIncrementId() . '(' . $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT) . ' ' . $name . ')';
	$guestCustomers[$order->getCustomerEmail()]['name'] = $name;
	if(!isset($guestCustomers[$order->getCustomerEmail()]['first_order_date']))
	{
		$guestCustomers[$order->getCustomerEmail()]['first_order_date'] = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
	}
	$guestCustomers[$order->getCustomerEmail()]['last_order_date'] = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
	
}
$guestCustomers = array_reverse($guestCustomers);
foreach($guestCustomers as $email => $customer)
{	
	$total[] = array($email, $customer['name'], $customer['first_order_date'], $customer['last_order_date'], $customer['orders']);
}
$total[] = array('', '', '', '', '');
$total[] = array('', '', '', '', '');
$total[] = array('REGISTERED CUSTOMERS', '', '', '', '');
$total[] = array('Email', 'Name', 'Created At', 'Updated At', '');
$registeredCustomersData = array_reverse($registeredCustomersData);
foreach($registeredCustomersData as $email => $customer)
{	
	$total[] = array($email, $customer['name'], $customer['created_at'], $customer['updated_at'], '');
}
$file = fopen('123.csv', 'w+');
foreach($total as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo 'DONE!!!';

/*function getOrderItemsData($order)
{
	$result = array();
	$result['order_number'] = $order->getIncrementId();
	$result['order_created_date'] = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
	$result['billing_address'] = $order->getBillingAddress()->getFormated(true);
	$result['shipping_address'] = $order->getShippingAddress()->getFormated(true);
	$result['status'] = $order->getStatusLabel();
	foreach($order->getAllItems() as $item)
	{
		$result['items'][] = array('sku' => $item->getSku(), 'price' => $item->getPrice(), 'qty' => $item->getQtyOrdered());
	}
	return $result;
}*/
?>