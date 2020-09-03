create table if not exists product_variant_master (
    id varchar(14) primary key,
    price float,
    sku varchar(10),
    inventory_item_id varchar(14),
    inventory_level int
);
create table if not exists product_variant_slave (
    id varchar(14) primary key,
    price float,
    sku varchar(10),
    inventory_item_id varchar(14),
    inventory_level int
)