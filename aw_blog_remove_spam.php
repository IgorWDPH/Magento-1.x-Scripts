<?php
ini_set('max_execution_time', 3600);
$dirPath = __DIR__;
$start = microtime(true);
require_once('app/Mage.php');
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$commentsCollection = Mage::getModel('blog/comment')->getCollection()->addApproveFilter(1);
foreach($commentsCollection as $comment)
{
	echo 'Comment ID: ' . $comment->getId() . PHP_EOL;
	$comment->delete();
}
?>