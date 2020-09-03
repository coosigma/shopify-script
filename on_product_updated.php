<?php

require 'vendor/autoload.php';
require 'inc/ShopifyClient.php';
require 'inc/passwd.php';

// Make sure that it is a POST request.
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}

//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json') != 0) {
    throw new Exception('Content type must be: application/json');
}

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));
$output = fopen("product_hook.json", "w") or die("Unable to open file!");
fwrite($output, $content);
fclose($output);
//Attempt to decode the incoming RAW post data from JSON.
// $content =
//     '{"id":5670648512679,"title":"Black Leather Bag","body_html":"\u003cp\u003eWomens black leather bag, with ample space. Can be worn over the shoulder, or remove straps to carry in your hand.\u003c\/p\u003e","vendor":"partners-demo","product_type":"","created_at":"2020-09-02T21:42:22-04:00","handle":"black-leather-bag","updated_at":"2020-09-03T06:01:49-04:00","published_at":"2020-09-02T21:42:22-04:00","template_suffix":"","published_scope":"web","tags":"women","admin_graphql_api_id":"gid:\/\/shopify\/Product\/5670648512678","variants":[{"id":35976784478374,"product_id":5670648512678,"title":"Default Title","price":"35.00","sku":"12","position":1,"inventory_policy":"deny","compare_at_price":null,"fulfillment_service":"manual","inventory_management":"shopify","option1":"Default Title","option2":null,"option3":null,"created_at":"2020-09-02T21:42:23-04:00","updated_at":"2020-09-03T06:01:49-04:00","taxable":true,"barcode":"","grams":0,"image_id":null,"weight":0.0,"weight_unit":"kg","inventory_item_id":37883005370534,"inventory_quantity":12,"old_inventory_quantity":12,"requires_shipping":true,"admin_graphql_api_id":"gid:\/\/shopify\/ProductVariant\/35976784478374"}],"options":[{"id":7222497869990,"product_id":5670648512678,"name":"Title","position":1,"values":["Default Title"]}],"images":[{"id":19107793404070,"product_id":5670648512678,"position":1,"created_at":"2020-09-02T21:42:22-04:00","updated_at":"2020-09-02T21:42:22-04:00","alt":null,"width":925,"height":617,"src":"https:\/\/cdn.shopify.com\/s\/files\/1\/0469\/3363\/9334\/products\/black-bag-over-the-shoulder_925x_05c5a84e-d332-467f-81d9-711996b7e8e7.jpg?v=1599097342","variant_ids":[],"admin_graphql_api_id":"gid:\/\/shopify\/ProductImage\/19107793404070"}],"image":{"id":19107793404070,"product_id":5670648512678,"position":1,"created_at":"2020-09-02T21:42:22-04:00","updated_at":"2020-09-02T21:42:22-04:00","alt":null,"width":925,"height":617,"src":"https:\/\/cdn.shopify.com\/s\/files\/1\/0469\/3363\/9334\/products\/black-bag-over-the-shoulder_925x_05c5a84e-d332-467f-81d9-711996b7e8e7.jpg?v=1599097342","variant_ids":[],"admin_graphql_api_id":"gid:\/\/shopify\/ProductImage\/19107793404070"}}';
$json = json_decode($content);

// $host = 'localhost';
// $user = 'root';
// $pass = '';
// $db = 'test';
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
$masterClient->update_column($mysqli, $json->variants, 'price');
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
