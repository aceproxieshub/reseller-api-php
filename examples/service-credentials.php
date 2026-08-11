<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$serviceCode = 'your-service-code';

// Retrieve the current credentials
$credentials = $client->services()->getCredentials($serviceCode);

var_dump($credentials);

// Updating credentials changes how the service is accessed. Review the values before uncommenting

/*
$request = new \Aceproxies\ResellerApi\Request\Service\UpdateCredentialsRequest(
    password: 'your-new-password',
    username: 'your-new-username',
);

$credentials = $client->services()->updateCredentials($serviceCode, $request);

var_dump($credentials);
*/
