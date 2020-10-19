<?php
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '3072M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php');
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

function getOrdersByPhone($phone)
{
	if(!trim($phone)) return array('error' => 'You forgot to set a phone number.');
	$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
	$sql = "SELECT parent_id FROM sales_flat_order_address WHERE telephone=" . $phone;
	$rows = $connection->fetchAll($sql);
	$orderIds = array();
	foreach($rows as $row)
	{
		if($row['parent_id'] && in_array($row['parent_id'], $orderIds)) continue;
		$orderIds[] = $row['parent_id'];
	}
	if(empty($orderIds)) return array('error' => 'Sorry, nothing has been found.');
	return $orderIds;
}
//Mage::getModel('sales/order')->load(24999);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Get Orders By Phone</title>
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

<div class="container">
	<?php
	if(isset($_POST['submited']) && $_POST['submited'] == 'submited'):
		$res = getOrdersByPhone($_POST['phone']);
		if($res['error']):?>
			<div class="alert alert-danger text-center" role="alert"><?php echo $res['error']; ?></div>		
		<?php endif;?>	
	<?php endif; ?>
	<div class="get-orders-data text-center">
		<h1>Orders By Phone</h1>
		<form method="post" action"">			
			<input type="hidden" id="submited" name="submited" value="submited">
			<div class="form-group">
				<label for="phone">Please, enter phone number:</label>
				<input type="text" class="form-control" name="phone" id="phone" placeholder="Enter phone number here">				
			</div>
			<button type="submit" class="btn btn-primary btn-lg">Search</button>
		</form>
	</div>
	<?php if($res && !$res['error']) foreach($res as $orderId): ?>
		<?php $order = Mage::getModel('sales/order')->load($orderId); ?>
		<table class="table">
			<thead>
				<tr>
					<th scope="col">Order ID</th>
					<th scope="col">Date</th>	
					<th scope="col">Name</th>
					<th scope="col">Email</th>
					<th scope="col">Ordered Products</th>
				</tr>
			</thead>
			<tbody>			
				<tr>
					<th scope="row"><?php echo $order->getIncrementId(); ?></th>
					<td><?php echo $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT); ?></td>
					<td><?php echo $order->getCustomerName(); ?></td>
					<td><?php echo $order->getCustomerEmail(); ?></td>
					<td><?php foreach($order->getAllVisibleItems() as $item): ?>
							<p><?php echo $item->getSku(); ?> - <?php echo $item->getName(); ?></p>
						<?php endforeach; ?>
					</td>
				</tr>			
			</tbody>
		</table>
	<?php endforeach; ?>
</div>
</body>
</html>