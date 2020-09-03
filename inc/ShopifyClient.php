<?php

use GuzzleHttp\Client;

class ShopifyClient
{
    public $table;
    public $client;
    public $location_id;
    function __construct($apikey, $passwd, $shop)
    {
        $this->table = $shop == 'cw-test-master' ? 'product_variant_master' : 'product_variant_slave';
        $this->location_d = $shop == 'cw-test-master' ? 53716844710 : 54041378973;
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
        return json_decode($response->getBody()->getContents())->products;
    }

    // Read product data from database
    public function read_from_table($dbi)
    {
        $sql = "Select * from $this->table";
        $result = $dbi->query($sql);
        if ($result->num_rows > 0) {
            return $result->fetch_all();
        } else {
            return [];
        }
    }
    // Write product data to database
    public function write_to_table($dbi, $products)
    {
        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $sql = "INSERT INTO $this->table (id, price, sku, inventory_item_id, inventory_level) VALUES ($variant->id, $variant->price, $variant->sku, $variant->inventory_item_id, $variant->inventory_quantity)";
                echo "processing id: " . $variant->id . "\n";
                if ($dbi->query($sql) === TRUE) {
                    echo "Record inserting successfully." . $dbi->error . "\n";
                } else {
                    echo "Error inserting record: " . $dbi->error . "\n";
                }
            }
        }
    }
    // Update product data in database
    public function update_table($dbi, $rows)
    {
        $sql = "UPDATE $this->table SET price = ?, inventory_level = ? WHERE id=?";
        foreach ($rows as $row) {
            echo "processing id: " . $row['id'] . "\n";
            if ($dbi->query($sql, [$row['price'], $row['inventory_level'], $row['id']]) === TRUE) {
                echo "Record updated successfully." . $dbi->error . "\n";
            } else {
                echo "Error updating record: " . $dbi->error . "\n";
            }
        }
    }
    // Update products to shopify shop
    public function write_to_shop($products)
    {
        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $url = "/admin/api/2020-07/variants/$variant->id.json";
                $json = [
                    "variant" => [
                        "price" => $variant->price
                    ]
                ];
                $responsePrice = $this->client->request('PUT', $url, [
                    'json'    => $json
                ]);
                $url = "/admin/api/2020-07/inventory_levels/set.json";
                $json = [
                    "location_id" => $this->location_id,
                    "inventory_item_id" => $variant->inventory_item_id,
                    "available" => $variant->inventory_quantity
                ];
                // Todo: Cannot set the inventory info.
                $responseInventory = $this->client->request('POST', $url, [
                    'json'    => $json
                ]);
                if ($responsePrice->getBody() && $responseInventory->getBody()) {
                    echo "Variant id: " . $variant->id . " is updated.\n";
                }
            }
        }
    }
    // Sync master and slave
    public static function overwrite_slave($master, $slave)
    {
        $skuInfo = [];
        foreach ($master as $product) {
            foreach ($product->variants as $variant) {
                if (property_exists($variant, 'sku')) {
                    $sku = $variant->sku;
                    $skuInfo[$sku]['price'] = $variant->price;
                    $skuInfo[$sku]['inventory_level'] = $variant->inventory_quantity;
                }
            }
        }
        foreach ($slave as $product) {
            foreach ($product->variants as $variant) {
                if (property_exists($variant, 'sku') && array_key_exists($variant->sku, $skuInfo)) {
                    $sku = $variant->sku;
                    $variant->price = $skuInfo[$sku]['price'];
                    $variant->inventory_quantity = $skuInfo[$sku]['inventory_level'];
                }
            }
        }
        return $slave;
    }
}
