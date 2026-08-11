<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$services = $client->services()->list(page: 1, limit: 20);

var_dump($services);
