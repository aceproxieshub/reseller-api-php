<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$version = $client->getApiVersion();

var_dump($version);
