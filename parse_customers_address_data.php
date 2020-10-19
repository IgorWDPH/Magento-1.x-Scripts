<?php
ini_set('max_execution_time', 3600);
$dirPath = __DIR__;
$start = microtime(true);
require_once('app/Mage.php');
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$customers = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*');

$counter = 0;
foreach($customers as $customer)
{	
	//Addresses
	foreach ($customer->getAddresses() as $address)
	{
		$addressArray = $address->toArray();
		if(!empty($addressArray) && $addressArray['region'] && trim($addressArray['region']) == 'n/a')
		{				
			$addressObj = Mage::getModel('customer/address')->load($addressArray['entity_id']);
			$addressObj->setData('region', '');
			//$addressObj->save();
			$counter++;
			/*echo '<pre>';
			print_r($addressObj->toArray());
			echo '</pre>';*/			
		}		
	}	
}
echo 'DONE: ' . $counter;
?>