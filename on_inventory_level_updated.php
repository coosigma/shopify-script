<?php

require 'vendor/autoload.php';
require 'inc/ShopifyClient.php';
require 'inc/passwd.php';

// Make sure that it is a POST request.
// if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
//     throw new Exception('Request method must be POST!');
// }

// //Make sure that the content type of the POST request has been set to application/json
// $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
// if (strcasecmp($contentType, 'application/json') != 0) {
//     throw new Exception('Content type must be: application/json');
// }

// //Receive the RAW post data.
// $content = trim(file_get_contents("php://input"));
// $output = fopen("inventory_hook.json", "w") or die("Unable to open file!");
// fwrite($output, $content);
// fclose($output);

$content = '{"inventory_item_id":271878346596884015,"location_id":53716844710,"available":null,"updated_at":"2020-09-03T08:44:10-04:00","admin_graphql_api_id":"gid:\/\/shopify\/InventoryLevel\/87892394150?inventory_item_id=271878346596884015"}';

$json = json_decode($content);

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

$masterClient = new ShopifyClient($masterAPI, $masterPasswd, $masterShop);
$slaveClient = new ShopifyClient($slaveAPI, $slavePasswd, $slaveShop);

// Read slave data from shop
$slaveProducts = $slaveClient->read_from_shop();
// Update master table 
$masterClient->update_column($mysqli, $json->variants, 'available');
// Get Overwrote slave
$updatedSlave = ShopifyClient::overwrite_slave([$json], $slaveProducts);

// Wirte to slave shop
$slaveClient->write_to_shop($updatedSlave);
// Get variant list
$updatedVariant = array_map(function ($value) {
    return $value->variants[0];
}, $updatedSlave);
// Write to slave table
$slaveClient->update_column($mysqli, $updatedVariant, 'price');
