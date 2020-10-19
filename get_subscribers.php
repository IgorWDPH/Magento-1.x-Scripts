<?php
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php');
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$total = array();
$total[] = array('subscriber_id', 'subscriber_email', 'subscriber_firstname', 'subscriber_lastname', 'store_id', 'change_status_at', 'customer_id', 'subscriber_status', 'addresses', 'subscriber_confirm_code');

$collection = Mage::getModel('newsletter/subscriber')->getCollection();

foreach($collection as $subscriber)
{
	$addressesData = '';
	$firstName = '';
	$lastName = '';
	if($subscriber->getData('customer_id'))
	{
		$customer = Mage::getModel('customer/customer')->load($subscriber->getData('customer_id'));
		$firstName = $customer->getData('firstname');
		$lastName = $customer->getData('lastname');
		$addresses = $customer->getAddresses();
		$addressesData = '';
		foreach($addresses as $address)
		{
			$addressesData .= print_r($address->getData(), true);
		}
	}
    $total[] = array($subscriber->getData('subscriber_id'), $subscriber->getData('subscriber_email'), ($subscriber->getData('subscriber_firstname')) ? $subscriber->getData('subscriber_firstname') : $firstName, ($subscriber->getData('subscriber_lastname')) ? $subscriber->getData('subscriber_lastname') : $lastName, $subscriber->getData('store_id'), $subscriber->getData('change_status_at'), $subscriber->getData('customer_id'), $subscriber->getData('subscriber_status'), $addressesData, $subscriber->getData('subscriber_confirm_code'));
}

$file = fopen('', 'w+');
foreach($total as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo 'DONE!!!';
?>