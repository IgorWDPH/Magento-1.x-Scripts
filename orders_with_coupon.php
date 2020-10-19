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
$guestOrders = array();
$registeredOrders = array();
$coupons = array('vapeten', 'tenvape', 'tenoff', 'fiveoff');
$orders = Mage::getModel('sales/order')->getCollection()->addFieldToSelect('*');
foreach($orders as $order)
{
	$orderIncrementId = $order->getIncrementId();
	$orderEmail = $order->getCustomerEmail();
	$orderGuest = $order->getCustomerIsGuest();
	foreach(explode(",", $order->getAppliedRuleIds()) as $ruleId)
	{
		$rule = Mage::getModel('salesrule/rule')->load($ruleId);
		if($rule->getCouponCode() && in_array(trim(strtolower($rule->getCouponCode())), $coupons))
		{			
			if($order->getCustomerIsGuest())
			{
				$guestOrders[$order->getCustomerEmail()]['ids'] .= $order->getIncrementId() . '||';
				$guestOrders[$order->getCustomerEmail()]['names'] .= $order->getBillingAddress()->getName() . '||';
				$guestOrders[$order->getCustomerEmail()]['coupons'] .= $rule->getCouponCode() . '||';
			}
			else
			{
				$registeredOrders[$order->getCustomerEmail()]['ids'] .= $order->getIncrementId() . '||';
				$registeredOrders[$order->getCustomerEmail()]['names'] .= $order->getBillingAddress()->getName() . '||';
				$registeredOrders[$order->getCustomerEmail()]['coupons'] .= $rule->getCouponCode() . '||';
			}
		}
	}
}
$total = array();
$total[] = array('REGISTERED:', count($registeredOrders), '', '');
$total[] = array('GUESTS:', count($guestOrders), '', '');
if(count($registeredOrders))
{
	$total[] = array('', '', '', '');
	$total[] = array('', '', '', '');
	$total[] = array('', '', '', '');
	$total[] = array('REGISTERED:', '', '', '');
	$total[] = array('Email', 'Increment Ids', 'Name(from billing address)', 'Coupon Codes');	
	foreach($registeredOrders as $email => $customer)
	{
		$total[] = array($email, $customer['ids'], $customer['names'], $customer['coupons']);
	}
}
if(count($guestOrders))
{
	$total[] = array('', '', '', '');
	$total[] = array('', '', '', '');
	$total[] = array('', '', '', '');
	$total[] = array('GUESTS:', '', '', '');
	$total[] = array('Email', 'Increment Ids', 'Name(from billing address)', 'Coupon Codes');	
	foreach($guestOrders as $email => $customer)
	{
		$total[] = array($email, $customer['ids'], $customer['names'], $customer['coupons']);
	}
}
$file = fopen('123.csv', 'w+');
foreach($total as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo 'GUESTS: ' . count($guestOrders) . '<br>';
echo 'REGISTERED: ' . count($registeredOrders) . '<br>';
echo 'DONE!';
//$guestOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_is_guest', 1)->addFieldToSelect('*');
?>