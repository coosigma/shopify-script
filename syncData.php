<?php
require 'vendor/autoload.php';
require './inc/ShopifyClient.php';
require './inc/passwd.php';

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'test';
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo "Error: Failed to make a MySQL connection \n";
    exit;
}
$masterClient = new ShopifyClient($masterAPI, $masterPasswd, $masterShop);
$slaveClient = new ShopifyClient($slaveAPI, $slavePasswd, $slaveShop);
$masterProducts = $masterClient->read_from_shop();
$masterClient->write_to_table($mysqli, $masterProducts);
$slaveProducts = $slaveClient->read_from_shop();
$slaveClient->write_to_table($mysqli, $slaveProducts);
