<?php

require_once ('lib/CURL.php');
require_once ('lib/Rahkaran.php');


$curl = new Curl();
$restServiceClient = new RestServiceClient('http://31.171.223.174:9000/HamoonKP', false);
$sessionId = $restServiceClient->Login($curl, 'admin', '@dmin', $cookie);
$curl->get("http://31.171.223.174:9000/HamoonKP/Retail/eSalesApi/ESalesService.svc/products?");

print_r($curl);