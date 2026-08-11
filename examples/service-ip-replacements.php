<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

/** @var Client $client */
$client = require __DIR__ . '/_bootstrap.php';

$serviceCode = 'your-service-code';

// List previous and pending replacements
$replacements = $client->services()->getIpReplacements($serviceCode);

var_dump($replacements);

// Check replacement availability and usage
$availableReplacements = $client->services()->getAvailableIpReplacements($serviceCode);
$replacementCount = $client->services()->getIpReplacementCount($serviceCode);

var_dump($availableReplacements, $replacementCount);

// List locations that can be requested for replacement IPs
$locations = $client->services()->getIpReplacementLocations($serviceCode);

var_dump($locations);

// Creating a replacement changes the IPs assigned to the service. Review the values before uncommenting

/*
$request = new \Aceproxies\ResellerApi\Request\Service\CreateIpReplacementRequest(['US', 'DE']);
$replacement = $client->services()->createIpReplacement($serviceCode, $request);

var_dump($replacement);
*/
