<?php
ini_set('memory_limit','512M');
ini_set('display_errors', 1);
ini_set('max_execution_time', 3600);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('type_id', array('eq' => 'configurable'))->addAttributeToSelect('*');	 
$result = array();
$result[] = array('ID', 'SKU', 'NAME', 'Stock Availability', 'Status(1-Enabled, 2-Disabled)');
foreach($productCollection as $product)
{
	$product = Mage::getModel('catalog/product')->load($product->getId());
	if (!$product->getStockItem()->getIsInStock())
	{
		$result[] = array($product->getId(), $product->getSku(), $product->getName(), $product->getStockItem()->getIsInStock(), $product->getStatus());		
	}
}
$file = fopen('', 'w+');
foreach($result as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo('Done');
?>