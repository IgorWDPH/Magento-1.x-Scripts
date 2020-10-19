<?php
ini_set('max_execution_time', 3600);
$dirPath = __DIR__;
$start = microtime(true);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$cdataElements = array();

$xmlDoc = new DOMDocument();

function createSection($xmlDoc, $cdataElements, $name, $value)
{
	if(in_array($name, $cdataElements))
	{
		$el = $xmlDoc->createElement($name);
		$el->appendChild($xmlDoc->createCDATASection($value));
		return $el;
	}
	return $xmlDoc->createElement($name, $value);
}

$root = $xmlDoc->appendChild($xmlDoc->createElement('categories'));

$catCollection = Mage::getModel('blog/cat')->getCollection();
foreach($catCollection as $cat)
{
	$catData = $cat->getData();
	$catSection = $root->appendChild($xmlDoc->createElement('category'));
	foreach($catData as $key => $value)
	{	
		$catSection->appendChild(createSection($xmlDoc, $cdataElements, $key, $value));
	}	
}

$xmlDoc->formatOutput = true;
$file_name = 'blog_categories.xml';
$xmlDoc->save($file_name);
echo 'Done' . PHP_EOL;
?>