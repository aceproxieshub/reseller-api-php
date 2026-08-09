<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi;

use Aceproxies\ResellerApi\Endpoint\Balance;
use Aceproxies\ResellerApi\Endpoint\BalanceInterface;
use Aceproxies\ResellerApi\Endpoint\Health;
use Aceproxies\ResellerApi\Endpoint\HealthInterface;
use Aceproxies\ResellerApi\Endpoint\Orders;
use Aceproxies\ResellerApi\Endpoint\OrdersInterface;
use Aceproxies\ResellerApi\Endpoint\Products;
use Aceproxies\ResellerApi\Endpoint\ProductsInterface;
use Aceproxies\ResellerApi\Endpoint\Services;
use Aceproxies\ResellerApi\Endpoint\ServicesInterface;
use Aceproxies\ResellerApi\Http\HttpClient;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Response\Version\VersionResponse;
use Aceproxies\ResellerApi\Validation\Assert;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;

final readonly class Client implements ClientInterface
{
    private const string BASE_URL = 'https://reseller.aceproxies.com/';

    private HttpClientInterface $httpClient;

    public function __construct(
        string $token,
        ?HttpClientInterface $httpClient = null,
        private string $baseUrl = self::BASE_URL,
    ) {
        Assert::nonEmptyString($token, 'API token');

        $this->httpClient = $httpClient ?? new HttpClient(SymfonyHttpClient::create(), token: $token);
    }

    public function health(): HealthInterface
    {
        return new Health($this->httpClient, $this->baseUrl);
    }

    public function balance(): BalanceInterface
    {
        return new Balance($this->httpClient, $this->baseUrl);
    }

    public function orders(): OrdersInterface
    {
        return new Orders($this->httpClient, $this->baseUrl);
    }

    public function products(): ProductsInterface
    {
        return new Products($this->httpClient, $this->baseUrl);
    }

    public function services(): ServicesInterface
    {
        return new Services($this->httpClient, $this->baseUrl);
    }

    public function getApiVersion(): string
    {
        $response = $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            rtrim($this->baseUrl, '/') . '/api/v1/version',
            VersionResponse::class,
        );

        return $response->version;
    }
}
