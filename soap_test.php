<?php

$apisoap_v2_url = '';

$username = '';
$password = '';

$client = new SoapClient($apisoap_v2_url);

//retreive session id from login
$session_id = $client->login($username, $password);

//call magentoInfo method
$result = $client->magentoInfo($session_id);

var_dump($result);