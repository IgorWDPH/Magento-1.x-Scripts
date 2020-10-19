<?php
ini_set('max_execution_time', 3600);
$dirPath = __DIR__;
$start = microtime(true);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
echo 'Start' . PHP_EOL;
$file = fopen('xyz_errors.csv', 'r');
echo 'File opened' . PHP_EOL;
$productIDs = array();
while(($line = fgetcsv($file)) !== FALSE)
{	
	//$line is an array of the csv elements
	$productIDs[] = $line;
}
echo 'File reading is done! ' . count($productIDs) . ' lines found!' . PHP_EOL;
if(empty($productIDs)) die('Product IDs Array is empty!');
$total = array();
$total[] = array('id', 'type', 'name', 'sku', 'strength', 'visibility', 'attention');
foreach($productIDs as $productId)
{
	$product = Mage::getModel('catalog/product')->load($productId[0]);
	$attention = false;
	if($product->getTypeId() == 'simple' && $product->getVisibility() == 4) $attention = true;
	$total[] = array($product->getId(), $product->getTypeId(), $product->getName(), $product->getSku(), $product->getAttributeText('strength'), $product->getVisibility(), $attention);
}
echo 'Data array creation is done!' . PHP_EOL;
$file = fopen('', 'w+');
foreach($total as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo 'Result file created!' . PHP_EOL;
?>