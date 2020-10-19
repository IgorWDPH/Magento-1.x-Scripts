<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 3600);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$productCollection = Mage::getModel('catalog/product')->getCollection();

$lines = array();
$lines[] = array('Name', 'Sku', 'Tier Prices');
foreach($productCollection as $product)
{	
	$tierPrices = $product->getTierPrice();
	if(empty($tierPrices))
	{
		continue;
	}
	$_product = Mage::getModel('catalog/product')->load($product->getId());
	$lines[] = array($_product->getName(), $_product->getSku(), '');
}

$file = fopen('', 'w+');
foreach($lines as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);

echo 'DONE!';
?>