<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi;

use Aceproxies\ResellerApi\Endpoint\Health;
use Aceproxies\ResellerApi\Endpoint\HealthInterface;
use Aceproxies\ResellerApi\Http\HttpClient;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use InvalidArgumentException;
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
        if ($token === '') {
            throw new InvalidArgumentException('The API token must not be empty.');
        }

        $this->httpClient = $httpClient ?? new HttpClient(SymfonyHttpClient::create());
    }

    public function health(): HealthInterface
    {
        return new Health($this->httpClient, $this->baseUrl);
    }
}
