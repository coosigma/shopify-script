<?php
require 'vendor/autoload.php';
require './inc/ShopifyClient.php';

$masterAPI = '3410137ef81e1bfd7e24d8ea6590837d';
$masterPasswd = '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';
$masterShop = 'cw-test-master';
$slaveAPI = '572cd8520ad236d5afcf0cde1615625d';
$slavePasswd = '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';
$slaveShop = 'cw-test-slave';
$host = 'mysql57';
$user = 'coosigma';
$pass = 'gqWZ92kV8wke';
$db = 'coosigma';
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo "Error: Failed to make a MySQL connection \n";
    exit;
}
$masterClient = new ShopifyClient($masterAPI, $masterPasswd, $masterShop);
$slaveClient = new ShopifyClient($slaveAPI, $slavePasswd, $slaveShop);
$res = $masterClient->read_from_shop();
print_r($res);
