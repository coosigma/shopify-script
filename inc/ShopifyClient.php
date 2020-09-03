<?php

use GuzzleHttp\Client;

class ShopifyClient
{
    public $base_uri;
    public $table;
    public $client;
    function __construct($apikey, $passwd, $shop)
    {
        $this->table = $this->shop == 'cw-test-master'? 'product_variant_master' : 'product_variant_slave';
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
        $table = $this->table;
        $sql = "Select * from $table";
        $result = $dbi->query($sql);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return [];
        }
    }
    // Write product data to database
    public function write_to_table($dbi, $data) {

    }
    // Update product data in database
    public function update_table($dbi, $rows) {
        $table = $this->table;
        $sql = "UPDATE $table SET price = ?, inventory_level = ? WHERE id=?";
        foreach ($rows as $row) {
            echo "processing id: " . $row['id'];
            if ($dbi->query($sql, $row['price'], $row['inventory_level'], $row['id']) === TRUE) {
                echo "Record updated successfully." . $dbi->error;
            } else {
                echo "Error updating record: " . $dbi->error;
            }
        }
   }
    // Sync master and slave
    public sync_data () {

    }
}
