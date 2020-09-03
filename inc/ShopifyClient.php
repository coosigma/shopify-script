<?php

use GuzzleHttp\Client;

class ShopifyClient
{
    public $base_uri;
    public $shop;
    public $client;
    function __construct($apikey, $passwd, $shop)
    {
       $this->shop = $shop; 
        $this->client = new Client([
            'base_uri' => "https://$apikey:$passwd@$shop.myshopify.com/",
            'timeout'  => 3.0,
        ]);
    }
    // Read data from shopify
    public function read_from_shop()
    {
        $url = '/admin/api/2020-07/products.json';
        $response = $this->client->request('GET', $url);
        return json_decode($response->getBody()->getContents());
    }

    // Read product data from database
    public function read_from_table($dbi)
    {
        $table = $this->shop . '_product';
        $sql = "Select * from $table";
        $result = $dbi->query($sql);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return [];
        }
    }
    // Update product data into database
    public write_to_table($dbi) {

    }
    // Sync master and slave
    public sync_data () {
        
    }
}
