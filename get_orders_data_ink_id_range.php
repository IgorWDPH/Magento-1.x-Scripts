<?php
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '3072M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php'); 
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
function getOrdersData($startingId, $endingId)
{
	$total = array();
	$total[] = array('Increment ID', 'Order Status', 'email', 'Name(Shipping Address)');
	$orders = Mage::getModel('sales/order')->getCollection()->addFieldToSelect('*')->addAttributeToFilter('increment_id',  array('gteq' => $startingId))->addAttributeToFilter('increment_id',  array('lteq' => $endingId));	
	$found = 0;
	foreach($orders as $order)
	{		
		$total[] = array($order->getIncrementId(), $order->getStatusLabel(), $order->getCustomerEmail(), $order->getShippingAddress()->getName());
		$found++;
	}
	$file = fopen('xy_orders_data.csv', 'w+');
	foreach($total as $line)
	{
		fputcsv($file, $line, ',', '"', '\\');
	}
	fclose($file);
	return $found;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Get Orders of Some Period</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
	<head>
	<style type="text/css">
	.get-orders-data { max-width: 320px; margin: 0 auto; }
	</style>
</head>
</head>
<body>

<div class="container text-center">	
	<?php
	//100052560 - 100052961
	if(isset($_POST['startNum']) && isset($_POST['endNum']) && intval($_POST['startNum']) < intval($_POST['endNum'])):	
	$res = getOrdersData(trim($_POST['startNum']), trim($_POST['endNum']));
	?>
	<div class="alert alert-success" role="alert">It is done. <?php echo $res; ?> orders found. File created.</div>
	<?php endif; ?>
	<div class="get-orders-data">
		<h1>Get Orders Data</h1>
		<form method="post" action"">
			<div class="form-group">
				<input type="number" class="form-control" name="startNum" id="startNum" placeholder="FROM increment ID">
				<input type="number" class="form-control" name="endNum" id="endNum" placeholder="TO increment ID">
			</div>
			<button type="submit" class="btn btn-primary btn-lg">Create Datasheet</button>
		</form>
	</div>
</div>
</body>
</html>