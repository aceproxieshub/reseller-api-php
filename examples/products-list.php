<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;
use Aceproxies\ResellerApi\Enum\ProductType;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$products = $client->products()->list(ProductType::ResidentialProxy);

var_dump($products);
