<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Tests\Integration;

use Aceproxies\ResellerApi\Http\HttpClientInterface;
use Closure;
use InvalidArgumentException;

final class RequestDelayHttpClient implements HttpClientInterface
{
    /** @var Closure(int): void */
    private Closure $sleeper;

    private bool $hasRequested = false;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly int $delayMicroseconds,
        ?Closure $sleeper = null,
    ) {
        if ($this->delayMicroseconds < 0) {
            throw new InvalidArgumentException('The request delay must not be negative.');
        }

        $this->sleeper = $sleeper ?? static function (int $microseconds): void {
            usleep($microseconds);
        };
    }

    /**
     * @param array<string, mixed> $options
     */
    public function request(
        string $method,
        string $url,
        string $responseClass,
        array $options = [],
    ): object {
        if ($this->hasRequested && $this->delayMicroseconds > 0) {
            ($this->sleeper)($this->delayMicroseconds);
        }

        $this->hasRequested = true;

        return $this->client->request($method, $url, $responseClass, $options);
    }
}
