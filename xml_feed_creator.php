<?php
try
{
	//$time_start = microtime(true);
	ini_set('max_execution_time', 3600);
	ini_set('memory_limit', '3072M');
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	require_once('app/Mage.php');
	umask(0);
	Mage::app('default');
	$currentStore = 11;
	Mage::app()->setCurrentStore($currentStore);
	$rootCategoryId = Mage::app()->getStore($currentStore)->getRootCategoryId();

	$xmlBegin = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
	$xmlBegin .= '<mywebstore>' . PHP_EOL;
	$xmlBegin .= '<created_at>' . date('Y-m-d H:i') . '</created_at>' . PHP_EOL;
	$xmlBegin .= '<products>' . PHP_EOL;
	$xmlEnd = '</products>' . PHP_EOL;
	$xmlEnd .= '</mywebstore>' . PHP_EOL;
	$xmlContent = '';

	$baseUrl = Mage::getBaseUrl();
	$productMediaDir = Mage::getBaseUrl('media') . 'catalog/product';
	$colorAttrOptions = Mage::getModel('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'color_2')->setStoreId($currentStore)->getSource()->getAllOptions();
	$colorOptions = array();
	//Mage::getModel('eav/entity_attribute')->load($_attribute->getAttributeId())->getAttributeCode();
	foreach($colorAttrOptions as $colorAttrOption)
	{
		$colorOptions[$colorAttrOption['value']] = $colorAttrOption['label'];
	}
	$categoriesCollection = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect(array('name', 'path'));
	$categories = array();
	foreach($categoriesCollection as $category)
	{
		$categories[$category->getId()] = array('name' => $category->getName(), 'path' => $category->getPath());
	}

	$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
	//$products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)->joinTable('cataloginventory/stock_item', 'product_id=entity_id', array('stock_status' => 'is_in_stock'))->addAttributeToSelect('stock_status')->addFinalPrice()->addCategoryIds()->getSelect();
	//sizes_axesouar_value, sizes_clothes_value, sizes_shoes_value, color_2
	$sql = "SELECT `super`.`product_id`, `super`.`attribute_id`, GROUP_CONCAT(`catalog_product_relation`.`child_id`) AS `child_products` FROM `catalog_product_super_attribute` AS `super` INNER JOIN `catalog_product_relation` ON (`catalog_product_relation`.`parent_id`=`super`.`product_id`) GROUP BY catalog_product_relation.parent_id";
	$confRelationsRows = $connection->fetchAll($sql);
	$confRelations = array();
	foreach($confRelationsRows as $row)
	{
		$confRelations[$row['product_id']] = array('attribute_id' => $row['attribute_id'], 'child_products' => explode(',', $row['child_products']));
	}
	$sql = "SELECT `e`.`entity_id`, `e`.`type_id`, `e`.`sizes_axesouar_value`, `e`.`sizes_clothes_value`, `e`.`sizes_shoes_value`, `e`.`color_2`, `cataloginventory_stock_item`.`is_in_stock` AS `stock_status` FROM `catalog_product_flat_" . $currentStore . "` AS `e` INNER JOIN `cataloginventory_stock_item` ON (cataloginventory_stock_item.product_id=e.entity_id) WHERE (e.status = '1') AND (e.visibility = '1') AND (cataloginventory_stock_item.is_in_stock = '1')";
	$childProductsRows = $connection->fetchAll($sql);
	$childProducts = array();
	foreach($childProductsRows as $product)
	{
		$colors = '';
		foreach(explode(',', $product['color_2']) as $color)
		{
			$colors .= ', ' . $colorOptions[$color];
		}
		$colors = substr($colors, 2);
		$childProducts[$product['entity_id']] = array('type_id' => $product['type_id'], 'sizes_axesouar_value' => $product['sizes_axesouar_value'], 'weight' => $product['weight'], 'sizes_clothes_value' => $product['sizes_clothes_value'], 'sizes_shoes_value' => $product['sizes_shoes_value'], 'color_2' => $colors);
	}
	$sql = "SELECT `e`.`entity_id`, `e`.`type_id`, `e`.`name`, `e`.`manufacturer_value`, `e`.`name`, `e`.`short_description`, `e`.`sku`, `e`.`small_image`, `e`.`url_path`, `e`.`weight`, `e`.`sizes_axesouar_value`, `e`.`sizes_clothes_value`, `e`.`sizes_shoes_value`, `e`.`color_2`, `cataloginventory_stock_item`.`is_in_stock` AS `stock_status`, `price_index`.`final_price`, GROUP_CONCAT(`catalog_category_product`.`category_id`) AS `categories` FROM `catalog_product_flat_" . $currentStore . "` AS `e` INNER JOIN `catalog_category_product` ON (catalog_category_product.product_id=e.entity_id) INNER JOIN `cataloginventory_stock_item` ON (cataloginventory_stock_item.product_id=e.entity_id) INNER JOIN `catalog_product_index_price` AS `price_index` ON (price_index.entity_id = e.entity_id AND price_index.website_id = '1' AND price_index.customer_group_id = 0) WHERE (e.status = '1') AND (e.visibility = '4') GROUP BY catalog_category_product.product_id";
	$mainProducts = $connection->fetchAll($sql);
	$result = array();
	foreach($mainProducts as $product)
	{
		$sizesAxesouar = '';
		$sizesClothes = '';
		$sizesShoes = '';
		$colors = '';
		if($product['type_id'] == 'configurable')
		{
			$sizes_axesouar_values = array();
			$sizes_clothes_values = array();
			$sizes_shoes_values = array();
			$color_2s = array();
			$children = $confRelations[$product['entity_id']]['child_products'];
			foreach($children as $child)
			{
				if($childProducts[$child]['sizes_axesouar_value'] && !in_array($childProducts[$child]['sizes_axesouar_value'], $sizes_axesouar_values)) $sizes_axesouar_values[] = $childProducts[$child]['sizes_axesouar_value'];
				if($childProducts[$child]['sizes_shoes_value'] && !in_array($childProducts[$child]['sizes_shoes_value'], $sizes_shoes_values)) $sizes_shoes_values[] = $childProducts[$child]['sizes_shoes_value'];
				if($childProducts[$child]['sizes_clothes_value'] && !in_array($childProducts[$child]['sizes_clothes_value'], $sizes_clothes_values)) $sizes_clothes_values[] = $childProducts[$child]['sizes_clothes_value'];
				if($childProducts[$child]['color_2'] && !in_array($childProducts[$child]['color_2'], $color_2s)) $color_2s[] = $childProducts[$child]['color_2'];
			}
			$sizesAxesouar = implode(', ', $sizes_axesouar_values);
			$sizesClothes = implode(', ', $sizes_clothes_values);
			$sizesShoes = implode(', ', $sizes_shoes_values);
			$colors = implode(', ', $color_2s);
		}
		else
		{
			$sizesAxesouar = $product['sizes_axesouar_value'];
			$sizesClothes = $product['sizes_clothes_value'];
			$sizesShoes = $product['sizes_shoes_value'];
			$colors = $product['color_2'];
		}
		$size = '';
		if($sizesAxesouar) $size = $sizesAxesouar;
		elseif($sizesClothes) $size = $sizesClothes;
		elseif($sizesShoes) $size = $sizesShoes;
		$productCats = explode(',', $product['categories']);
		$categoryPathArray = array();
		$categoryPath = '';
		$categoryId = '';
		foreach($productCats as $cat)
		{
			$catPath = explode('/', $categories[$cat]['path']);
			if(count($catPath) > count($categoryPathArray)) $categoryPathArray = $catPath;
		}
		if(count($categoryPathArray))
		{
			foreach($categoryPathArray as $cat)
			{
				if(intval($cat) <= intval($rootCategoryId)) continue;
				$categoryPath .= ' > ' . $categories[$cat]['name'];
				$categoryId = $cat;
			}
			$categoryPath = substr($categoryPath, 3);
		}
		$stockStatus = 'N';
		$availability = 'Μη Διαθέσιμο';
		if($product['stock_status'])
		{
			$stockStatus = 'Y';
			$availability = 'Διαθέσιμο';
		}
		$xmlContent .=  '	<product>' . PHP_EOL;
		$xmlContent .=  '		<UniqueID><![CDATA[' . $product['entity_id'] . ']]></UniqueID>' . PHP_EOL;
		$xmlContent .=  '		<name><![CDATA[' .  htmlspecialchars($product['name'] . ' ' . $product['sku']) . ']]></name>' . PHP_EOL;
		$xmlContent .=  '		<link><![CDATA[' .  htmlspecialchars($baseUrl . $product['url_path']) . ']]></link>' . PHP_EOL;
		$xmlContent .=  '		<image><![CDATA[' .  htmlspecialchars($productMediaDir . $product['small_image']) . ']]></image>' . PHP_EOL;
		$xmlContent .=  '		<category ><![CDATA[' .  $categoryPath . ']]></category >' . PHP_EOL;
		$xmlContent .=  '		<categoryid ><![CDATA[' . $categoryId . ']]></categoryid >' . PHP_EOL;
		$xmlContent .=  '		<price_with_vat><![CDATA[' . round($product['final_price'], 2) . ']]></price_with_vat>' . PHP_EOL;
		$xmlContent .=  '		<manufacturer ><![CDATA[' .  htmlspecialchars($product['manufacturer_value']) . ']]></manufacturer >' . PHP_EOL;
		$xmlContent .=  '		<description ><![CDATA[' . htmlspecialchars(preg_replace('/\s+/', ' ', strip_tags($product['short_description']))) . ']]></description >' . PHP_EOL;
		$xmlContent .=  '		<mpn ><![CDATA[' .  htmlspecialchars($product['sku']) . ']]></mpn >' . PHP_EOL;
		$xmlContent .=  '		<size ><![CDATA[' .  htmlspecialchars($size) . ']]></size >' . PHP_EOL;
		$xmlContent .=  '		<color ><![CDATA[' .  htmlspecialchars($colors) . ']]></color >' . PHP_EOL;
		$xmlContent .=  '		<weight ><![CDATA[' . $product['weight'] . ']]></weight >' . PHP_EOL;
		$xmlContent .=  '		<InStock><![CDATA[' . $stockStatus . ']]></InStock>' . PHP_EOL;
		$xmlContent .=  '		<Availability><![CDATA[' . $availability . ']]></Availability>' . PHP_EOL;
		$xmlContent .=  '	</product>' . PHP_EOL;
	}
	$fl = fopen(Mage::getBaseDir() . '/feed.xml', 'w+');
	fwrite($fl, $xmlBegin . $xmlContent . $xmlEnd);
	fclose($fl);	
}
catch(Exception $e)
{
	$report = $e->getMessage();
	$toEmail1 = '';
	$toEmail2 = '';
	$subject = 'XML Feed Creator Failed';
	mail($toEmail1, $subject, $report);
	mail($toEmail2, $subject, $report);
	//echo $e->getMessage();
	return false;
}