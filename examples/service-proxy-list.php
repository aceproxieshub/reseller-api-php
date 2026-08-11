<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$proxies = $client->services()->getProxyList('your-service-code');

var_dump($proxies);
