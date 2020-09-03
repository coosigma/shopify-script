<?php
require 'vendor/autoload.php';
require './inc/ShopifyClient.php';

$masterAPI = '3410137ef81e1bfd7e24d8ea6590837d';
$masterPasswd = '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';
$masterShop = 'cw-test-master';
$host = 'mysql57';
$user = 'coosigma';
$pass = 'gqWZ92kV8wke';
$db = 'coosigma';
$mysqli = new mysqli($host, $user, $pass, $db);

$masterClient = new ShopifyClient($masterAPI, $masterPasswd, $masterShop);
$res = $masterClient->read_from_shop();
print_r($res);
