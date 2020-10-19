<?php
ini_set('max_execution_time', 3600);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(array('name', 'sku'));
$result = array();
$result[] = array('ID', 'TYPE', 'SKU', 'NAME', 'QTY', 'Stock Availability', 'Children Info');
$simpleProductsInfo = array();
$confProductsInfo = array();
foreach ($productCollection as $product)
{
	if($product->getTypeId() == 'simple')
	{
		$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);				
		if(!$stock->getIsInStock() && (int)$stock->getQty())
		{
			$simpleProductsInfo[] = array($product->getId(), $product->getTypeId(), $product->getSku(), $product->getName(), $stock->getQty(), $stock->getIsInStock(), 'none');			
		}
	}
	elseif($product->getTypeId() == 'configurable')
	{
		$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
		if(!$stock->getIsInStock())
		{
			$childIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());
			$hasAvaliableChild = false;
			$childrendInfo = '';
			foreach($childIds as $childIdsGroup)
			{
				foreach($childIdsGroup as $childId)
				{
					$childProduct = Mage::getModel('catalog/product')->load($childId);
					$childStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);
					if((int)$childStock->getQty())
					{
						$hasAvaliableChild = true;					
					}
					$childrendInfo .= ' Product ID: "' . $childProduct->getId() . '" Product SKU: "' . $childProduct->getSku() . '" Product Name: "' . $childProduct->getName() . '" Product QTY: "' . $childStock->getQty() . '" Stock Availability: "' . boolval($childStock->getIsInStock()) . '";';
				}
			}
			if($hasAvaliableChild)
			{
				$confProductsInfo[] = array($product->getId(), $product->getTypeId(), $product->getSku(), $product->getName(), 'none', $stock->getIsInStock(), $childrendInfo);
			}
		}
	}
}
$result = array_merge($result, $simpleProductsInfo, $confProductsInfo);
$file = fopen('123.csv', 'w+');
foreach($result as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo('Done! Simple products: ' . count($simpleProductsInfo) . '. Conf. products: ' . count($confProductsInfo));
?>