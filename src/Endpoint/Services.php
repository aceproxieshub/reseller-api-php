<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\ServiceListResponse;
use InvalidArgumentException;

final readonly class Services implements ServicesInterface
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
     * @throws InvalidArgumentException
     */
    public function list(?int $page = null, ?int $limit = null): ServiceListResponse
    {
        $query = [];

        if ($page !== null) {
            $this->assertPositive($page, 'Page');
            $query['page'] = $page;
        }

        if ($limit !== null) {
            $this->assertPositive($limit, 'Limit');
            $query['limit'] = $limit;
        }

        $url = rtrim($this->baseUrl, '/') . '/api/v1/services';

        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            $url,
            ServiceListResponse::class,
        );
    }

    private function assertPositive(int $value, string $name): void
    {
        if ($value < 1) {
            throw new InvalidArgumentException($name . ' must be greater than zero.');
        }
    }
}
