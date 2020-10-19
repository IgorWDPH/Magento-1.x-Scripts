<?php
ini_set('max_execution_time', 3600);
$dirPath = __DIR__;
$start = microtime(true);
require_once('app/Mage.php');
umask(0);
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$cdataElements = array('title', 'post_content', 'comment');
$mediaElements = array('image', 'thumb');
$cmsConstentElements = array('post_content', 'short_content');
$baseMediaUrl = Mage::getBaseUrl('media');
//$baseUrl = Mage::getBaseUrl();
$baseUrl = '';

$xmlDoc = new DOMDocument();

function createSection($xmlDoc, $cdataElements, $name, $value, $mediaElements, $baseMediaUrl, $cmsConstentElements, $baseUrl)
{
	$sectionValue = $value;
	if($value && in_array($name, $mediaElements))
	{		
		$sectionValue = $baseMediaUrl . $value;
	}
	if($value && in_array($name, $cmsConstentElements))
	{
		$sectionValue = str_replace('{{store url="', $baseUrl, $sectionValue);
		$sectionValue = str_replace('{{media url="', $baseMediaUrl, $sectionValue);
		$sectionValue = str_replace('"}}', '', $sectionValue);
	}
	if(in_array($name, $cdataElements))
	{
		$el = $xmlDoc->createElement($name);
		$el->appendChild($xmlDoc->createCDATASection($sectionValue));
		return $el;
	}
	return $xmlDoc->createElement($name, $sectionValue);
}

$root = $xmlDoc->appendChild($xmlDoc->createElement('posts'));

$postsCollection = Mage::getModel('blog/post')->getCollection();
$postsCollection->getSelect()->join(
    array('post_cat' => 'aw_blog_post_cat'),
	'main_table.post_id=post_cat.post_id',
    'post_cat.cat_id'
);
foreach($postsCollection as $post)
{
	$postData = $post->getData();
	$postSection = $root->appendChild($xmlDoc->createElement('post'));	
	foreach($postData as $key => $value)
	{	
		$postSection->appendChild(createSection($xmlDoc, $cdataElements, $key, $value, $mediaElements, $baseMediaUrl, $cmsConstentElements, $baseUrl));
	}	
	$commentsCollection = Mage::getModel('blog/comment')
		->getCollection()
		->addPostFilter($postData['post_id'])
		->addApproveFilter(2);
	$commentsSection = $postSection->appendChild($xmlDoc->createElement('comments_collection'));
	foreach($commentsCollection as $comment)
	{
		$commentData = $comment->getData();
		$commentSection = $commentsSection->appendChild($xmlDoc->createElement('comment_data'));
		foreach($commentData as $key => $value)
		{
			$commentSection->appendChild(createSection($xmlDoc, $cdataElements, $key, $value, $mediaElements, $baseMediaUrl, $cmsConstentElements, $baseUrl));
		}		
	}	
}
$xmlDoc->formatOutput = true;
$file_name = 'blog_posts.xml';
$xmlDoc->save($file_name);
echo 'Done' . PHP_EOL;
?>