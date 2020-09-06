<?php
require 'vendor/autoload.php';
require './inc/ShopifyClient.php';
require './inc/passwd.php';

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'test';
// Connect to db
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo "Error: Failed to make a MySQL connection \n";
    exit;
}
// Get clients
$masterClient = new ShopifyClient($masterAPI, $masterPasswd, $masterShop);
$slaveClient = new ShopifyClient($slaveAPI, $slavePasswd, $slaveShop);
// Get product data from master shop
$masterProducts = $masterClient->read_from_shop();
// Check if there is a master table in the db, if not, write one
$masterTable = $masterClient->read_from_table($mysqli);
if (count($masterTable) === 0) {
    $masterClient->write_to_table($mysqli, $masterProducts);
}
// Get product data from slave shop
$slaveProducts = $slaveClient->read_from_shop();
// Get overwrote slave data according to master data
$updatedSlave = ShopifyClient::overwrite_slave($masterProducts, $slaveProducts);
// Write to slave shop
$slaveClient->write_to_shop($updatedSlave);
// Write to slave table
$slaveClient->write_to_table($mysqli, $updatedSlave);
