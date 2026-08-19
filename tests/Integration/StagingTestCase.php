<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Client;
use Aceproxies\ResellerApi\Enum\ProductType;
use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Http\HttpClient;
use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Aceproxies\ResellerApi\Request\Order\CreateOrderItem;
use Aceproxies\ResellerApi\Request\Order\CreateOrderRequest;
use Aceproxies\ResellerApi\Response\Order\CreateOrderResponse;
use Aceproxies\ResellerApi\Response\Product\ProductResponse;
use Aceproxies\ResellerApi\Response\Service\ServiceResponse;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;

abstract class StagingTestCase extends TestCase
{
    private const DEFAULT_REQUEST_DELAY_MILLISECONDS = 1_000;

    private const PRODUCT_TYPES = [
        ProductType::DedicatedProxy,
        ProductType::StaticResidentialProxy,
        ProductType::ResidentialProxy,
        ProductType::PaygResidentialProxy,
    ];

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $baseUrl = $this->environment('ACEPROXIES_STAGING_BASE_URL');

        if (rtrim($baseUrl, '/') === 'https://reseller.aceproxies.com') {
            throw new RuntimeException('The staging suite refuses to target the production API.');
        }

        $token = $this->environment('ACEPROXIES_STAGING_TOKEN');
        $httpClient = new HttpClient(
            SymfonyHttpClient::create(),
            token: $token,
        );

        $this->client = new Client(
            $token,
            new RequestDelayHttpClient($httpClient, $this->requestDelayMicroseconds()),
            baseUrl: $baseUrl,
        );
    }

    protected function fullMode(): bool
    {
        return getenv('ACEPROXIES_STAGING_MODE') === 'full';
    }

    protected function requireFullMode(): void
    {
        if (!$this->fullMode()) {
            self::markTestSkipped('Mutation checks require full staging mode.');
        }
    }

    /**
     * @template T
     * @param list<T> $items
     * @return T
     */
    protected function randomItem(array $items, string $resource): mixed
    {
        if ($items === []) {
            self::fail('The staging API returned no ' . $resource . ' to test.');
        }

        return $items[random_int(0, count($items) - 1)];
    }

    /**
     * @return list<ServiceResponse>
     */
    protected function services(?string $type = null): array
    {
        $services = $this->client->services()->list(limit: 100)->items;

        if ($type === null) {
            return $services;
        }

        return array_values(array_filter(
            $services,
            static fn (ServiceResponse $service): bool => strtolower((string) $service->type) === strtolower($type),
        ));
    }

    /**
     * @template T
     * @param callable(string): ?T $operation
     * @return array{service: ServiceResponse, response: T}
     */
    protected function findService(callable $operation, string $endpoint): array
    {
        foreach ($this->services() as $service) {
            try {
                $response = $operation($service->code);
            } catch (ApiException $exception) {
                if ($this->isUnsupportedMutation($exception)) {
                    continue;
                }

                throw $exception;
            }

            if ($response !== null) {
                return ['service' => $service, 'response' => $response];
            }
        }

        self::fail('No staging service supports the ' . $endpoint . ' endpoint.');
    }

    /**
     * @template T
     * @param callable(string): ?T $operation
     * @return array{service: ServiceResponse, response: T}
     */
    protected function mutateService(callable $operation, string $endpoint): array
    {
        foreach ($this->services() as $service) {
            try {
                $response = $operation($service->code);

                if ($response === null) {
                    continue;
                }

                return ['service' => $service, 'response' => $response];
            } catch (ApiException $exception) {
                if ($this->isUnsupportedMutation($exception)) {
                    continue;
                }

                throw $exception;
            }
        }

        self::fail('No staging service supports the ' . $endpoint . ' mutation.');
    }

    protected function isUnsupportedMutation(ApiException $exception): bool
    {
        return $exception->statusCode === HttpClientInterface::HTTP_NOT_FOUND
            || strtolower(trim((string) $exception->apiMessage)) === 'unsupported action';
    }

    /**
     * @return list<ProductType>
     */
    protected function productTypes(): array
    {
        return self::PRODUCT_TYPES;
    }

    protected function createOrder(ProductType $productType): CreateOrderResponse
    {
        if (!in_array($productType, self::PRODUCT_TYPES, true)) {
            throw new RuntimeException('Unsupported staging product type: ' . $productType->value);
        }

        $products = $this->client->products()->list($productType)->items;
        $product = $this->randomItem($products, $productType->value . ' products');

        $durationId = $productType === ProductType::PaygResidentialProxy
            ? null
            : $this->randomItem($product->durations ?? [], $productType->value . ' product durations')->id;

        $options = match ($productType) {
            ProductType::PaygResidentialProxy => ['trafficGb' => 3],
            ProductType::ResidentialProxy => [],
            ProductType::DedicatedProxy, ProductType::StaticResidentialProxy => [
                'proxyType' => 'http',
                'authType' => 'combined',
                'locations' => [$this->randomItem($this->locationIds($product), $productType->value . ' product locations')],
            ],
        };

        return $this->client->orders()->create(new CreateOrderRequest([
            new CreateOrderItem(
                productId: $product->id,
                quantity: 1,
                durationId: $durationId,
                options: $options,
                addons: [],
            ),
        ]));
    }
    /**
     * @return list<string>
     */
    private function locationIds(ProductResponse $product): array
    {
        $locations = $product->options['locations'] ?? null;

        if (!is_array($locations)) {
            return [];
        }

        if (isset($locations['id']) && is_string($locations['id']) && trim($locations['id']) !== '') {
            return [$locations['id']];
        }

        $ids = [];

        foreach ($locations as $location) {
            if (is_string($location) && trim($location) !== '') {
                $ids[] = $location;
                continue;
            }

            if (is_array($location) && isset($location['id']) && is_string($location['id'])) {
                $ids[] = $location['id'];
            }
        }

        return array_values(array_unique($ids));
    }

    private function environment(string $name): string
    {
        $value = getenv($name);

        if (!is_string($value) || trim($value) === '') {
            throw new RuntimeException('Missing required environment variable: ' . $name);
        }

        return $value;
    }

    private function requestDelayMicroseconds(): int
    {
        $value = getenv('ACEPROXIES_STAGING_REQUEST_DELAY_MS');

        if ($value === false || trim($value) === '') {
            return self::DEFAULT_REQUEST_DELAY_MILLISECONDS * 1_000;
        }

        if (!ctype_digit($value)) {
            throw new RuntimeException(
                'ACEPROXIES_STAGING_REQUEST_DELAY_MS must be a non-negative integer.',
            );
        }

        return (int) $value * 1_000;
    }
}
