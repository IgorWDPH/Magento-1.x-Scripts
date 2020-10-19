<?php
ini_set('max_execution_time', 3600);
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$collection = Mage::getModel("sales/order")->getCollection();
$collection->getSelect()->join(
    'sales_flat_order_item', 
    '`sales_flat_order_item`.order_id=`main_table`.entity_id', 
        array(
              'skus' => new Zend_Db_Expr('group_concat(`sales_flat_order_item`.sku SEPARATOR ", ")')
             )
 )->group('main_table.customer_id');

$total = array();
$total[] = array('Email', 'Name', 'Subscription', 'Creation Date', 'DOB', 'Address', 'Purchased products');
$customersBoth = array();
foreach ($collection as $data)
{
    $customersBoth[$data->getCustomerEmail()] = $data->getSkus();
}
$customers = Mage::getModel('customer/customer')->getCollection();
foreach ($customers as $item)
{
	$line = array();
	$customer = Mage::getModel('customer/customer')->load($item->getId());
	$line[] = $customer->getEmail();
	$line[] = $customer->getName();	
	if(Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail())->getId())
	{
		$line[] = 'subscribed';
	}
	else
	{
		$line[] = '';
	}
	$line[] = $customer->getCreatedAt();
	$line[] = $customer->getDob();	
	$resultAddress = '';
	foreach ($customer->getAddresses() as $address)
	{
		$data = $address->toArray();
		if(!empty($data))
		{
			$resultAddress .= 'Adress:[';
			$resultAddress .= 'City: ' . $data['city'] . '; ';
			$resultAddress .= 'Postcode: ' . $data['postcode'] . '; ';
			$resultAddress .= 'Telephone: ' . $data['telephone'] . '; ';
			$resultAddress .= 'Street: ' . $data['street'] . '; ';
			$resultAddress .= ']';			
		}
	}
	$line[] = $resultAddress;
	$line[] = isset($customersBoth[$customer->getEmail()]) ? $customersBoth[$customer->getEmail()] : '';	
	$total[] = $line;
}
$file = fopen('123.csv', 'w+');
foreach($total as $line)
{
	fputcsv($file, $line, ',', '"', '\\');
}
fclose($file);
echo 'DONE!!!';
?>