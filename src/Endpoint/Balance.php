<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\BalanceResponse;

final readonly class Balance implements BalanceInterface
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
    public function getBalance(): BalanceResponse
    {
        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/balance',
            BalanceResponse::class,
        );
    }
}
