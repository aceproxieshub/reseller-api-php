<?php

declare(strict_types=1);

// Updating a service changes its protocol or authentication method
$client = require __DIR__ . '/_bootstrap.php';


$request = new \Aceproxies\ResellerApi\Request\Service\UpdateServiceRequest(
    protocol: \Aceproxies\ResellerApi\Enum\Protocol::http,
    auth: new \Aceproxies\ResellerApi\Request\Service\UpdateServiceAuthPayload('ip'),
);

$client->services()->update('your-service-code', $request);
