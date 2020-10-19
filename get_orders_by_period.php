<?php
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '3072M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$fromDate = '2020-06-15 00:00:00';
$toDate = '2020-07-10 23:59:59';

$orders = Mage::getModel('sales/order')->getCollection()
		->addAttributeToSelect('*')
		->addAttributeToFilter('created_at', array('gteq'=>$fromDate))
		->addAttributeToFilter('created_at', array('lteq'=>$toDate));

$total = array();
$total[] = array('Name', 'Email', 'Order ID', ' Product ID (SKU)', 'Created At');
	
foreach($orders as $order)
{	
	$items = array();
	foreach($order->getAllItems() as $item)
	{
		$sku = $item->getSku();
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());		
		if($product && $product->getTypeId() == 'simple')
		{
			$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
			if(!$parentIds)	$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
			if(isset($parentIds[0]))
			{
				$parent = Mage::getModel('catalog/product')->load($parentIds[0]);
				$sku = $parent->getSku();
			}
		}
		if(!in_array($item->getSku(), $items))
		{ 
			$items[] = $item->getSku();
			$total[] = array($order->getShippingAddress()->getData('firstname'), $order->getCustomerEmail(), $order->getIncrementId(), $sku, $order->getCreatedAt());	
		}
    }	
	
}

$file = fopen('', 'w+');
foreach($total as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo 'DONE!';
?>