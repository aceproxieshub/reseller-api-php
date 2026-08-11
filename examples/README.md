# API examples

These examples demonstrate the public operations provided by the Aceproxies Reseller API client.

Install the Composer dependencies and expose your API token as an environment variable before running an example:

```bash
composer install
export ACEPROXIES_API_TOKEN='your-api-token'
php examples/health.php
```

Replace values such as `your-service-code`, `your-order-id`, and `your-product-id` before making a request. Operations that create, update, or delete resources are commented out by default. Review their values and remove the surrounding comment only when you intend to modify API resources.

Some responses contain proxy or service credentials. Avoid writing their output to application logs or other shared locations.

## General

- [Get API version](api-version.php)
- [Check API health](health.php)
- [Get account balance](balance.php)

## Products

- [List products](products-list.php)
- [List product types](product-types.php)

## Orders

- [List orders](orders-list.php)
- [Find an order](orders-find.php)
- [Create an order](orders-create.php)

## Services

- [List services](services-list.php)
- [Find a service](service-find.php)
- [Get service bandwidth](service-bandwidth.php)
- [Manage service credentials](service-credentials.php)
- [Manage whitelisted IPs](service-whitelisted-ips.php)
- [Manage IP replacements](service-ip-replacements.php)
- [Manage prolongations](service-prolongations.php)
- [Get a service proxy list](service-proxy-list.php)
- [Update a service](service-update.php)

## Residential services

- [List countries](residential-countries.php)
- [List rotation intervals](residential-rotation-intervals.php)
- [Manage proxy requests](residential-proxy-requests.php)
