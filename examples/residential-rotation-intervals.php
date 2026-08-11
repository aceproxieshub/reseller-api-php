<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$rotationIntervals = $client->services()->residential()->rotationIntervals();

var_dump($rotationIntervals);
