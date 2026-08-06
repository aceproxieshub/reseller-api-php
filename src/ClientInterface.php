<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi;

use Aceproxies\ResellerApi\Endpoint\HealthInterface;

interface ClientInterface
{
    public function health(): HealthInterface;

    public function getApiVersion(): string;
}
