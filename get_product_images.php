<?php
ini_set('max_execution_time', 3600);
$dirPath = __DIR__;
$start = microtime(true);
require_once('app/Mage.php');
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$productMediaDir = Mage::getBaseDir('media') . '/catalog/product';
$destDir = Mage::getBaseDir('media') . '/uvwxyz/';
if (!file_exists($destDir)) {
    mkdir($destDir, 777, true);
}

$csvFile = file('products missing images 1.csv');
$data = [];
foreach ($csvFile as $line) {
	$data[] = str_getcsv($line);
}
unset($data[0]);

$skuColumnIdx = 1;
$result = array();
$result[] = array('ID', 'SKU', 'NAME', 'IMAGE');
foreach($data as $item)
{
	$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', trim($item[$skuColumnIdx]));
	if(!$_product)
	{
		echo 'Not found, SKU: "' . $item[$skuColumnIdx] . '"' . PHP_EOL;
		continue;
	}
	//echo 'Found, SKU: "' . $item[1] . '"' . PHP_EOL;
	//echo $productMediaDir . $_product->getImage() . PHP_EOL;
	$imagePath = $_product->getImage();
	if(!$imagePath or $imagePath == 'no_selection') echo 'No image, SKU: "' . $item[$skuColumnIdx] . '"' . PHP_EOL;
	$imageName = array_pop(explode('/', $imagePath));	
	$copied = false;
	//echo $productMediaDir . $imagePath . ' -> ' . $destDir . $imageName . PHP_EOL;
	if($imagePath && $imageName != 'no_selection' && exec('cp ' . $productMediaDir . $imagePath . ' ' . $destDir . $imageName)) $copied = true;	
	$result[] = array($_product->getId(), $_product->getSku(), $_product->getName(), $imageName);
}
$file = fopen('', 'w+');
foreach($result as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo count($data) . ' products found!';
echo('Done') . PHP_EOL;
/*echo '<pre>';
print_r($header);
print_r($data);
echo '</pre>';*/
?>