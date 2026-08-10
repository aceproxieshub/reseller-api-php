<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Http;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\TransportException;

interface HttpClientInterface
{
    public const int MAX_ATTEMPTS = 3;

    public const int INITIAL_BACKOFF_MICROSECONDS = 100_000;
    public const int MAX_BACKOFF_MICROSECONDS = 1_000_000;

    public const int HTTP_TOO_MANY_REQUESTS = 429;
    public const int HTTP_OK = 200;
    public const int HTTP_UNAUTHORIZED = 401;
    public const int HTTP_NOT_FOUND = 404;
    public const int HTTP_INTERNAL_SERVER_ERROR = 500;
    public const int HTTP_SERVER_ERROR_MIN = 500;
    public const int HTTP_SERVICE_UNAVAILABLE = 503;

    public const string METHOD_GET = 'GET';
    public const string METHOD_POST = 'POST';
    public const string METHOD_PATCH = 'PATCH';
    public const string METHOD_PUT = 'PUT';
    public const string METHOD_DELETE = 'DELETE';

    public const string HEADER_ACCEPT = 'Accept';
    public const string HEADER_ACCEPT_JSON = 'application/json';
    public const string HEADER_AUTHORIZATION = 'Authorization';
    public const string HEADER_RETRY_AFTER = 'retry-after';

    /**
     * @template T of object
     * @param class-string<T> $responseClass
     * @param array<string, mixed> $options
     * @return T
     * @throws ApiException
     * @throws TransportException
     */
    public function request(
        string $method,
        string $url,
        string $responseClass,
        array $options = [],
    ): object;
}
