<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Response\HealthResponse;

interface HealthInterface
{
    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     */
    public function getHealth(): HealthResponse;
}
