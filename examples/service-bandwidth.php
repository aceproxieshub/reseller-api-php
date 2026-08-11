<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$bandwidth = $client->services()->getBandwidth('your-service-code');

var_dump($bandwidth);
