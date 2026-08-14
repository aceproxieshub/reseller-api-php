# Aceproxies Reseller API official PHP library

[![Build Status](https://github.com/aceproxieshub/reseller-api-php/actions/workflows/quality.yml/badge.svg?branch=master)](https://github.com/aceproxieshub/reseller-api-php/actions/workflows/)
[![PHP Compatibility](https://github.com/aceproxieshub/reseller-api-php/actions/workflows/php-compatibility.yml/badge.svg?branch=master)](https://github.com/aceproxieshub/reseller-api-php/actions/workflows/php-compatibility.yml)
[![Latest Stable Version](https://poser.pugx.org/aceproxieshub/reseller-api-php/v)](https://packagist.org/packages/aceproxieshub/reseller-api-php)
[![PHP Version Require](https://poser.pugx.org/aceproxieshub/reseller-api-php/require/php)](https://packagist.org/packages/aceproxieshub/reseller-api-php)
[![License](https://poser.pugx.org/aceproxieshub/reseller-api-php/license)](https://packagist.org/packages/aceproxieshub/reseller-api-php)

The Aceproxies PHP library provides straightforward access to Reseller API from applications written in PHP language.
Our mission is to provide you clear contracts through pre-defined set of classes and API resources for frictionless integration with your services.

## Requirements

PHP 8.3 and later, with the cURL, JSON, and Mbstring extensions.

We stay in sync with the official [PHP supported versions](https://www.php.net/supported-versions.php) and schedule version sunsets according to security support.

## Composer

You can install the library via [Composer](https://getcomposer.org/). Run the following command:

```bash
composer require aceproxieshub/reseller-api-php
```

## Dependencies

Our library requires the following extensions in order to function properly:

- [`curl`](https://secure.php.net/manual/en/book.curl.php)
- [`json`](https://secure.php.net/manual/en/book.json.php)
- [`mbstring`](https://secure.php.net/manual/en/book.mbstring.php)

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Getting Started

```php
use Aceproxies\ResellerApi\Client;

$client = new Client('your-api-token');
$health = $client->health()->getHealth();

echo $health->status;
```

## Examples

Runnable examples for every public API operation are available in the [examples guide](./examples/README.md)


## Retry policy

The default client applies a 10-second idle timeout and a 30-second maximum request duration. Read-only `GET` requests are retried up to three times for rate limits, server errors, and transport failures using bounded exponential backoff. Mutating requests are never retried automatically as they might lead to undesired consequences.

## Error

- `InvalidArgumentException` is thrown before transport when a request value is invalid.
- `Aceproxies\ResellerApi\Exception\ApiException` exposes the HTTP status, optional API message, and raw response body.
- `Aceproxies\ResellerApi\Exception\InvalidResponseException` represents malformed or incompatible success payloads.
- `Aceproxies\ResellerApi\Exception\TransportException` represents exhausted connection or transport failures and retains the underlying exception.

Treat exception bodies and response DTOs containing proxy or service credentials as sensitive data and do not log them indiscriminately.

## Security

Report vulnerabilities privately as described in [SECURITY.md](SECURITY.md).


[composer]: https://getcomposer.org/
[curl]: http://curl.haxx.se/docs/caextract.html
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer

