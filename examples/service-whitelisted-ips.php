<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$serviceCode = 'your-service-code';

// List the currently whitelisted IP addresses
$whitelistedIps = $client->services()->getWhitelistedIps($serviceCode);

var_dump($whitelistedIps);

// Adding or deleting an IP changes access to the service. Review the values before uncommenting

/*
$request = new \Aceproxies\ResellerApi\Request\Service\CreateWhitelistedIpRequest('203.0.113.10');
$whitelistedIp = $client->services()->addWhitelistedIp($serviceCode, $request);

var_dump($whitelistedIp);

$client->services()->deleteWhitelistedIp($serviceCode, '203.0.113.10');
*/
