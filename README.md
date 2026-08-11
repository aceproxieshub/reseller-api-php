# Aceproxies Reseller API official PHP library

The Aceproxies PHP library provides straightforward access to Reseller API from applications written in PHP language.
Our mission is to provide you clear contracts through pre-defined set of classes and API resources for frictionless integration with your services.

## Requirements

PHP 8.3 and later

we're staying in sync with official [PHP Supported Versions](https://www.php.net/supported-versions.php) and scheduling supported versions sunset accordingly to security support.

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

[composer]: https://getcomposer.org/
[curl]: http://curl.haxx.se/docs/caextract.html
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
