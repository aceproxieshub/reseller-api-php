<?php

declare(strict_types=1);

// Creating an order charges the account. Review the values before uncommenting

/*
$client = require __DIR__ . '/_bootstrap.php';

$request = new \Aceproxies\ResellerApi\Request\Order\CreateOrderRequest([
    new \Aceproxies\ResellerApi\Request\Order\CreateOrderItem(
        productId: 'your-product-id',
        quantity: 1,
        durationId: 'your-duration-id',
        addons: [],
        options: [],
    ),
]);

$order = $client->orders()->create($request);

var_dump($order);
*/
