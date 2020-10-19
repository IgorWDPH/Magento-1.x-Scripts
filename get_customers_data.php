<?php
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '3072M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

function getCustomersData($registered, $guests, $whoOrdered)
{
	if($registered)
	{
		$registeredCustomers = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('firstname')->addAttributeToSelect('lastname');
		$registeredCustomersData = array();
		foreach($registeredCustomers as $customer)
		{
			if($whoOrdered)
			{
				$order = Mage::getResourceModel('sales/order_collection')
					->addFieldToSelect('entity_id')
					->addFieldToFilter('customer_id', $customer->getId())
					->setCurPage(1)
					->setPageSize(1)
					->getFirstItem();
				if(!$order->getId())	
				{
					//echo $customer->getEmail() . '<br>';
					continue;
				}				
			}
			$registeredCustomersData[$customer->getEmail()]['name'] = $customer->getName();
			$registeredCustomersData[$customer->getEmail()]['created_at'] = $customer->getCreatedAt();
			$registeredCustomersData[$customer->getEmail()]['updated_at'] = $customer->getUpdatedAt();
		}
	}
	if($guests)
	{
		$orders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_is_guest', 1)->addFieldToSelect('*');
		$guestCustomers = array();	
		foreach($orders as $order)
		{
			if(array_key_exists($order->getCustomerEmail(), $registeredCustomersData)) continue;
			$name = $order->getBillingAddress()->getName();
			$guestCustomers[$order->getCustomerEmail()]['orders'] .= ' ' . $order->getIncrementId() . '(' . $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT) . ' ' . $name . ')';
			$guestCustomers[$order->getCustomerEmail()]['name'] = $name;
			if(!isset($guestCustomers[$order->getCustomerEmail()]['first_order_date']))
			{
				$guestCustomers[$order->getCustomerEmail()]['first_order_date'] = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
			}
			$guestCustomers[$order->getCustomerEmail()]['last_order_date'] = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
			
		}
	}
	if($guests)
	{
		$total = array();
		$total[] = array('GUESTS', '', '', '', '');
		$total[] = array('', '', '', '', '');
		$total[] = array('Email', 'Name(from billing address)', 'First Order Date', 'Last Order Date', 'Orders');
		$guestCustomers = array_reverse($guestCustomers);
		foreach($guestCustomers as $email => $customer)
		{	
			$total[] = array($email, $customer['name'], $customer['first_order_date'], $customer['last_order_date'], $customer['orders']);
		}
	}
	if($registered)
	{
		$total[] = array('', '', '', '', '');
		$total[] = array('', '', '', '', '');
		$total[] = array('REGISTERED CUSTOMERS', '', '', '', '');
		$total[] = array('Email', 'Name', 'Created At', 'Updated At', '');
		$registeredCustomersData = array_reverse($registeredCustomersData);
		foreach($registeredCustomersData as $email => $customer)
		{	
			$total[] = array($email, $customer['name'], $customer['created_at'], $customer['updated_at'], '');
		}
	}
	$file = fopen('', 'w+');
	foreach($total as $line)
	{
		fputcsv($file, $line, ',', '"', '\\');
	}
	fclose($file);
	return array('guests' => count($guestCustomers), 'registered' => count($registeredCustomersData));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Get Customers Data</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>	
	<style type="text/css">
	.get-orders-data { max-width: 480px; margin: 0 auto; padding: 15px;	background: #f5f5f5; border-radius: 15px; }
	</style>
</head>
<body>

<div class="container text-center">	
	<?php	
	if(isset($_POST['submited']) && $_POST['submited'] == 'submited'):	
	$res = getCustomersData($_POST['registered'], $_POST['guests'], $_POST['registered_with_orders']);
	?>
	<div class="alert alert-success" role="alert">It is done. <?php echo $res['guests']; ?> guests and <?php echo $res['registered']; ?> registered found. File created.</div>
	<?php endif; ?>
	<div class="get-orders-data">
		<h1>Get Customers Data</h1>
		<form method="post" action"">			
			<input type="hidden" id="submited" name="submited" value="submited">
			<div class="text-left">
				<div class="form-check">
				  <input class="form-check-input" type="checkbox" checked value="guests" name="guests" id="guests">
				  <label class="form-check-label" for="guests">Add guest customers</label>
				</div>
				<div class="form-check">
				  <input class="form-check-input" type="checkbox" checked value="registered" name="registered" id="registered">
				  <label class="form-check-label" for="registered">Add registered users</label>
				</div>
				<div class="form-check">
				  <input class="form-check-input" type="checkbox" checked value="registered_with_orders" name="registered_with_orders" id="registered-with-orders">
				  <label class="form-check-label" for="registered-with-orders">Only users who ordered before</label>
				</div>
			</div>
			<button type="submit" class="btn btn-primary btn-lg">Create Datasheet</button>
		</form>
	</div>
</div>
</body>
</html>