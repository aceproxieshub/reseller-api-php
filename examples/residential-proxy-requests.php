<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$serviceCode = 'your-residential-service-code';
$proxyRequestId = 'your-proxy-request-id';

// List all proxy requests for the service
$proxyRequests = $client->services()->residential()->proxyRequests($serviceCode);

var_dump($proxyRequests);

// Find one proxy request
$proxyRequest = $client->services()->residential()->findProxyRequest($serviceCode, $proxyRequestId);

var_dump($proxyRequest);

// Retrieve the proxies created for a request.
$proxies = $client->services()->residential()->getProxyList($serviceCode, $proxyRequestId);

var_dump($proxies);

// Creating or deleting a proxy request changes the service. Review the values before uncommenting

/*
$request = new \Aceproxies\ResellerApi\Request\Service\Residential\CreateProxyRequest(
    countryId: 1,
    proxyCount: 10,
    rotationInterval: \Aceproxies\ResellerApi\Enum\RotationInterval::all,
);

$proxyRequest = $client->services()->residential()->createProxyRequest($serviceCode, $request);

var_dump($proxyRequest);

$deletedProxyRequest = $client->services()->residential()->deleteProxyRequest(
    $serviceCode,
    $proxyRequestId,
);

var_dump($deletedProxyRequest);
*/
