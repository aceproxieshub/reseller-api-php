<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$serviceCode = 'your-service-code';

// List the available prolongation options
$prolongations = $client->services()->getProlongations($serviceCode);

var_dump($prolongations);


// Creating a prolongation charges the account. Review the values before uncommenting

/*
$request = new \Aceproxies\ResellerApi\Request\Service\CreateProlongationRequest(
    durationId: 'your-duration-id',
    quantity: 1,
);

$prolongation = $client->services()->createProlongation($serviceCode, $request);

var_dump($prolongation);
*/
