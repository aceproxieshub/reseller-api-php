<?php

declare(strict_types=1);

use Aceproxies\ResellerApi\Client;

require __DIR__ . '/../vendor/autoload.php';

$token = getenv('ACEPROXIES_RESELLER_API_TOKEN');

if (!is_string($token) || $token === '') {
    throw new RuntimeException('Set the ACEPROXIES_RESELLER_API_TOKEN environment variable before running an example.');
}

return new Client($token);
