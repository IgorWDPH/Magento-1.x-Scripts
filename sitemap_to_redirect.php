<?php
libxml_use_internal_errors(TRUE);
$objXmlDocument = simplexml_load_file('sitemap.xml');
if($objXmlDocument === FALSE)
{
    echo 'There were errors parsing the XML file.\n';
    foreach(libxml_get_errors() as $error)
	{
        echo $error->message;
    }
    exit;
}
$objJsonDocument = json_encode($objXmlDocument);
$arrOutput = json_decode($objJsonDocument, TRUE);

$resultFile = fopen('111.txt', 'w');
foreach($arrOutput['url'] as $item)
{
	fwrite($resultFile, 'Redirect 301 ' . str_replace('https://', '', $item['loc']) . ' ' . $item['loc'] . PHP_EOL);
}
fclose($resultFile);
echo 'Done!';
?>