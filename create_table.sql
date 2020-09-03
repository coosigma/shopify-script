create table product_variant_master (
    id int(14) unsigned primary key,
    price float,
    sku varchar(10),
    inventory_item_id int(14) unsigned,
    inventory_level int,
);

create table product_variant_slave (
    id int(14) unsigned primary key,
    price float,
    sku varchar(10),
    inventory_item_id int(14) unsigned,
    inventory_level int,
);