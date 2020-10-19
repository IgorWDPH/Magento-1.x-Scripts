<?php
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '3072M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('app/Mage.php');
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

function processProduct($productSku)
{
	$productSku = trim($productSku);
	if(!$productSku) return array('error' => 'You forgot to set a SKU');
	$productId = Mage::getModel('catalog/product')->getIdBySku($productSku);
	if(!$productId) return array('error' => 'There are no products with this SKU');
	$product = Mage::getModel('catalog/product')->load($productId);
	$res = array();
	if($product->getTypeId() == 'simple')
	{
		$res[$product->getName() . '(' . $productSku . ')'] = getOrdersData($productId);
	}
	elseif($product->getTypeId() == 'configurable')
	{
		$children = $product->getTypeInstance()->getUsedProducts($product);
		foreach ($children as $child)
		{
			$res[$child->getName() . '(' . $child->getSku() . ')'] = getOrdersData($child->getId());
        }
	}
	else
	{
		return array('error' => 'Sorry :(, this script works only with simple or configurable products');
	}
	return $res;
}
function getOrdersData($productId)
{	
	$orders = array();
	$collection = Mage::getResourceModel('sales/order_item_collection')
		->addAttributeToFilter('product_id', array('eq' => $productId))
		->load();
	foreach($collection as $orderItem) 
	{
		$orders = array($orderItem->getOrder()->getIncrementId() => $orderItem->getOrder()) + $orders;
	}
	
	return $orders;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Get Orders Data</title>
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
		$res = processProduct($_POST['product_sku']);
		if($res['error']):?>
			<div class="alert alert-danger text-center" role="alert"><?php echo $res['error']; ?></div>		
		<?php endif;?>	
	<?php endif; ?>
	<div class="get-orders-data text-center">
		<h1>Get Orders Data</h1>
		<form method="post" action"">			
			<input type="hidden" id="submited" name="submited" value="submited">
			<div class="form-group">
				<label for="product_sku">Product Sku:</label>
				<input type="text" class="form-control" name="product_sku" id="product_sku" placeholder="Enter product SKU">				
			</div>
			<button type="submit" class="btn btn-primary btn-lg">Get Data</button>
		</form>
	</div>
	<?php foreach($res as $productName => $productData): ?>
		<h4><?php echo $productName; ?></h4>
		<table class="table">
			<thead>
				<tr>
					<th scope="col">Order ID</th>
					<th scope="col">Date</th>				
				</tr>
			</thead>
			<tbody>
			<?php foreach($productData as $orderId => $order): ?>
			<tr>
				<th scope="row"><?php echo $orderId; ?></th>
				<td><?php echo $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT); ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endforeach; ?>
</div>
</body>
</html>