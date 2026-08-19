<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\Health\HealthResponse;

final readonly class Health implements HealthInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
    ) {
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     */
    public function getHealth(): HealthResponse
    {
        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            sprintf(
                '%s/api/v1/health',
                rtrim($this->baseUrl, '/'),
            ),
            HealthResponse::class,
        );
    }
}
