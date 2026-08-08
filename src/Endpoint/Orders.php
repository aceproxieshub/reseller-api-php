<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Request\CreateOrderRequest;
use Aceproxies\ResellerApi\Response\CreateOrderResponse;
use Aceproxies\ResellerApi\Response\OrderDetailsResponse;
use Aceproxies\ResellerApi\Response\OrderListResponse;
use InvalidArgumentException;

final readonly class Orders implements OrdersInterface
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
    public function list(?int $page = null, ?int $limit = null): OrderListResponse
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

        $url = rtrim($this->baseUrl, '/') . '/api/v1/orders';

        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            $url,
            OrderListResponse::class,
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     */
    public function create(CreateOrderRequest $request): CreateOrderResponse
    {
        return $this->httpClient->request(
            HttpClientInterface::METHOD_POST,
            rtrim($this->baseUrl, '/') . '/api/v1/orders',
            CreateOrderResponse::class,
            ['json' => $request->toArray()],
        );
    }

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     */
    public function get(string $id): OrderDetailsResponse
    {
        if ($id === '') {
            throw new InvalidArgumentException('The order ID must not be empty.');
        }

        return $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/orders/' . rawurlencode($id),
            OrderDetailsResponse::class,
        );
    }

    private function assertPositive(int $value, string $name): void
    {
        if ($value < 1) {
            throw new InvalidArgumentException($name . ' must be greater than zero.');
        }
    }
}
