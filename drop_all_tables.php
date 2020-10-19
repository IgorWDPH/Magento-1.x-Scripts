<?php
/*Mysql:
host => localhost
dbname => dbbxm8yk9k3rsn
username => u8us793mzdcpb
password => wwap3x5wv83d*/
$host = '';
$database = '';
$user = '';
$password = '';
$mysqli = new mysqli($host, $user, $password, $database);
$mysqli->query('SET foreign_key_checks = 0');
if ($result = $mysqli->query('SHOW TABLES'))
{
    while($row = $result->fetch_array(MYSQLI_NUM))
    {		
		echo $mysqli->query('DROP TABLE IF EXISTS ' . $row[0]) . ' ';
		echo $row[0] . PHP_EOL;
    }
}
// DROP VIEW
echo 'DROP VIEW:' . PHP_EOL;
if ($result = $mysqli->query('SHOW TABLES'))
{
    while($row = $result->fetch_array(MYSQLI_NUM))
    {		
		echo $mysqli->query('DROP VIEW IF EXISTS ' . $row[0]) . ' ';
		echo $row[0] . PHP_EOL;
    }
}
$mysqli->query('SET foreign_key_checks = 1');
$mysqli->close();
?>