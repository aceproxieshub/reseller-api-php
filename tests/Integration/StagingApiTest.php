<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Client;
use Aceproxies\ResellerApi\Enum\Protocol;
use Aceproxies\ResellerApi\Enum\RotationInterval;
use Aceproxies\ResellerApi\Request\Order\CreateOrderItem;
use Aceproxies\ResellerApi\Request\Order\CreateOrderRequest;
use Aceproxies\ResellerApi\Request\Service\CreateIpReplacementRequest;
use Aceproxies\ResellerApi\Request\Service\CreateProlongationRequest;
use Aceproxies\ResellerApi\Request\Service\CreateWhitelistedIpRequest;
use Aceproxies\ResellerApi\Request\Service\Residential\CreateProxyRequest;
use Aceproxies\ResellerApi\Request\Service\UpdateCredentialsRequest;
use Aceproxies\ResellerApi\Request\Service\UpdateServiceRequest;
use Aceproxies\ResellerApi\Response\Balance\BalanceResponse;
use Aceproxies\ResellerApi\Response\Health\HealthResponse;
use Aceproxies\ResellerApi\Response\Order\CreateOrderResponse;
use Aceproxies\ResellerApi\Response\Order\OrderListResponse;
use Aceproxies\ResellerApi\Response\Order\OrderResponse;
use Aceproxies\ResellerApi\Response\Product\ProductListResponse;
use Aceproxies\ResellerApi\Response\Product\ProductTypesResponse;
use Aceproxies\ResellerApi\Response\Service\BandwidthResponse;
use Aceproxies\ResellerApi\Response\Service\CreateProlongationResponse;
use Aceproxies\ResellerApi\Response\Service\CredentialsResponse;
use Aceproxies\ResellerApi\Response\Service\DetailResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementCountResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementLocationsResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementResponse;
use Aceproxies\ResellerApi\Response\Service\IpReplacementsResponse;
use Aceproxies\ResellerApi\Response\Service\ListResponse;
use Aceproxies\ResellerApi\Response\Service\ProlongationsResponse;
use Aceproxies\ResellerApi\Response\Service\ProxyListResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\CountriesResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyListResponse as ResidentialProxyListResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\ProxyRequestsResponse;
use Aceproxies\ResellerApi\Response\Service\Residential\RotationIntervalsResponse;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpResponse;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpsResponse;
use JsonException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class StagingApiTest extends TestCase
{
    private Client $client;

    /** @var array<string, mixed> */
    private array $config;

    public function testReadOnlyContract(): void
    {
        self::assertInstanceOf(HealthResponse::class, $this->client->health()->getHealth());
        self::assertNotSame('', $this->client->getApiVersion());
        self::assertInstanceOf(BalanceResponse::class, $this->client->balance()->getBalance());

        $orders = $this->client->orders();
        self::assertInstanceOf(OrderListResponse::class, $orders->list(limit: 1));
        self::assertInstanceOf(OrderResponse::class, $orders->find($this->string('orderId')));

        $products = $this->client->products();
        self::assertInstanceOf(ProductListResponse::class, $products->list());
        self::assertInstanceOf(ProductTypesResponse::class, $products->types());

        $services = $this->client->services();
        self::assertInstanceOf(ListResponse::class, $services->list(limit: 1));
        self::assertInstanceOf(DetailResponse::class, $services->find($this->string('services.detail')));
        self::assertInstanceOf(BandwidthResponse::class, $services->getBandwidth($this->string('services.bandwidth')));
        self::assertInstanceOf(CredentialsResponse::class, $services->getCredentials($this->string('services.credentials')));
        self::assertInstanceOf(WhitelistedIpsResponse::class, $services->getWhitelistedIps($this->string('services.whitelist')));
        self::assertInstanceOf(IpReplacementsResponse::class, $services->getIpReplacements($this->string('services.ipReplacement')));
        self::assertInstanceOf(IpReplacementCountResponse::class, $services->getAvailableIpReplacements($this->string('services.ipReplacement')));
        self::assertInstanceOf(IpReplacementCountResponse::class, $services->getIpReplacementCount($this->string('services.ipReplacement')));
        self::assertInstanceOf(IpReplacementLocationsResponse::class, $services->getIpReplacementLocations($this->string('services.ipReplacement')));
        self::assertInstanceOf(ProlongationsResponse::class, $services->getProlongations($this->string('services.prolongation')));
        self::assertInstanceOf(ProxyListResponse::class, $services->getProxyList($this->string('services.proxyList')));

        $residential = $services->residential();
        $residentialCode = $this->string('residential.serviceCode');
        $proxyRequestId = $this->string('residential.proxyRequestId');
        self::assertInstanceOf(CountriesResponse::class, $residential->countries());
        self::assertInstanceOf(RotationIntervalsResponse::class, $residential->rotationIntervals());
        self::assertInstanceOf(ProxyRequestsResponse::class, $residential->proxyRequests($residentialCode));
        self::assertInstanceOf(ProxyRequestResponse::class, $residential->findProxyRequest($residentialCode, $proxyRequestId));
        self::assertInstanceOf(ResidentialProxyListResponse::class, $residential->getProxyList($residentialCode, $proxyRequestId));
    }

    public function testMutationContract(): void
    {
        if ($this->environment('ACEPROXIES_STAGING_MODE') !== 'full') {
            self::markTestSkipped('Mutation checks require full staging mode.');
        }

        $orderItem = new CreateOrderItem(
            productId: $this->string('mutations.order.productId'),
            quantity: $this->integer('mutations.order.quantity'),
            durationId: $this->nullableString('mutations.order.durationId'),
            addons: $this->map('mutations.order.addons'),
            options: $this->map('mutations.order.options'),
        );
        self::assertInstanceOf(
            CreateOrderResponse::class,
            $this->client->orders()->create(new CreateOrderRequest([$orderItem])),
        );

        $services = $this->client->services();
        $services->update(
            $this->string('mutations.update.serviceCode'),
            new UpdateServiceRequest(protocol: Protocol::from($this->string('mutations.update.protocol'))),
        );
        self::assertInstanceOf(
            CredentialsResponse::class,
            $services->updateCredentials(
                $this->string('mutations.credentials.serviceCode'),
                new UpdateCredentialsRequest(
                    $this->string('mutations.credentials.password'),
                    $this->nullableString('mutations.credentials.username'),
                ),
            ),
        );

        $whitelistCode = $this->string('mutations.whitelist.serviceCode');
        $whitelistIp = $this->string('mutations.whitelist.ip');
        $whitelistAdded = false;
        try {
            self::assertInstanceOf(
                WhitelistedIpResponse::class,
                $services->addWhitelistedIp($whitelistCode, new CreateWhitelistedIpRequest($whitelistIp)),
            );
            $whitelistAdded = true;
        } finally {
            if ($whitelistAdded) {
                $services->deleteWhitelistedIp($whitelistCode, $whitelistIp);
            }
        }

        self::assertInstanceOf(
            IpReplacementResponse::class,
            $services->createIpReplacement(
                $this->string('mutations.ipReplacement.serviceCode'),
                new CreateIpReplacementRequest($this->stringList('mutations.ipReplacement.locations')),
            ),
        );
        self::assertInstanceOf(
            CreateProlongationResponse::class,
            $services->createProlongation(
                $this->string('mutations.prolongation.serviceCode'),
                new CreateProlongationRequest(
                    $this->string('mutations.prolongation.durationId'),
                    $this->integer('mutations.prolongation.quantity'),
                ),
            ),
        );

        $residential = $services->residential();
        $residentialCode = $this->string('mutations.residential.serviceCode');
        $created = $residential->createProxyRequest(
            $residentialCode,
            new CreateProxyRequest(
                $this->integer('mutations.residential.countryId'),
                $this->integer('mutations.residential.proxyCount'),
                RotationInterval::from($this->string('mutations.residential.rotationInterval')),
            ),
        );

        self::assertInstanceOf(
            ProxyRequestResponse::class,
            $residential->deleteProxyRequest($residentialCode, $created->id),
        );
    }

    protected function setUp(): void
    {
        if ($this->environment('ACEPROXIES_STAGING_CONFIRM') !== 'staging-only') {
            throw new RuntimeException('Staging confirmation is missing.');
        }

        $baseUrl = $this->environment('ACEPROXIES_STAGING_BASE_URL');

        if (rtrim($baseUrl, '/') === 'https://reseller.aceproxies.com') {
            throw new RuntimeException('The staging suite refuses to target the production API.');
        }

        $decoded = json_decode(
            $this->environment('ACEPROXIES_STAGING_CONFIG'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        if (!is_array($decoded)) {
            throw new JsonException('The staging configuration must be a JSON object.');
        }

        $config = [];

        foreach ($decoded as $key => $value) {
            if (!is_string($key)) {
                throw new JsonException('The staging configuration must use string keys.');
            }

            $config[$key] = $value;
        }

        $this->config = $config;
        $this->client = new Client(
            $this->environment('ACEPROXIES_STAGING_TOKEN'),
            baseUrl: $baseUrl,
        );
    }

    private function environment(string $name): string
    {
        $value = getenv($name);

        if (!is_string($value) || trim($value) === '') {
            throw new RuntimeException('Missing required environment variable: ' . $name);
        }

        return $value;
    }

    private function value(string $path): mixed
    {
        $value = $this->config;

        foreach (explode('.', $path) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                throw new RuntimeException('Missing staging configuration value: ' . $path);
            }

            $value = $value[$part];
        }

        return $value;
    }

    private function string(string $path): string
    {
        $value = $this->value($path);

        if (!is_string($value) || trim($value) === '') {
            throw new RuntimeException('Expected a non-empty string at: ' . $path);
        }

        return $value;
    }

    private function nullableString(string $path): ?string
    {
        $value = $this->value($path);

        if ($value !== null && !is_string($value)) {
            throw new RuntimeException('Expected a string or null at: ' . $path);
        }

        return $value;
    }

    private function integer(string $path): int
    {
        $value = $this->value($path);

        if (!is_int($value)) {
            throw new RuntimeException('Expected an integer at: ' . $path);
        }

        return $value;
    }

    /** @return array<string, mixed> */
    private function map(string $path): array
    {
        $value = $this->value($path);

        if (!is_array($value) || array_is_list($value) && $value !== []) {
            throw new RuntimeException('Expected an object at: ' . $path);
        }

        $map = [];

        foreach ($value as $key => $item) {
            if (!is_string($key)) {
                throw new RuntimeException('Expected an object at: ' . $path);
            }

            $map[$key] = $item;
        }

        return $map;
    }

    /** @return list<string> */
    private function stringList(string $path): array
    {
        $value = $this->value($path);

        if (!is_array($value) || !array_is_list($value)) {
            throw new RuntimeException('Expected a string list at: ' . $path);
        }

        foreach ($value as $item) {
            if (!is_string($item)) {
                throw new RuntimeException('Expected a string list at: ' . $path);
            }
        }

        return $value;
    }
}
