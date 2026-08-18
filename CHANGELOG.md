# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-08-18

### Changed
- Upgrade phpunit/php-code-coverage from 14.3.0 to 14.3.1

### Added

- Optional service-type filtering through `Client::services()->list(type: $type)`
- Optional product-type filtering through `Client::products()->list($type)`
- Service `type` fields on service list and detail responses

## [1.0.0] - 2026-08-13

This is the first release of the Aceproxies Reseller API PHP Library

### Added

- Added whitespace and IP-address validation for public request inputs
- String-backed `Protocol` and `RotationInterval` enums for service request inputs
- API version method Client::getApiVersion()
- Health endpoint support through Client::health()->getHealth()
- Balance endpoint support with bearer authorization headers
- Orders list, creation, and details endpoint support
- Products list and types endpoint support
- Services list endpoint support
- Service detail lookup through `Client::services()->find()`
- Service updates through `Client::services()->update()`
- Service bandwidth lookup through `Client::services()->getBandwidth()`
- Service auth credentials lookup through `Client::services()->getCredentials()`
- Service auth credentials updates through `Client::services()->updateCredentials()`
- Service auth whitelisted IP management through `Client::services()->getWhitelistedIps()`, `addWhitelistedIp()`, and `deleteWhitelistedIp()`
- Service IP replacement listing, creation, quota, count, and location endpoints
- Service prolongation listing and creation through `Client::services()->getProlongations()` and `createProlongation()`
- Service proxy list lookup through `Client::services()->getProxyList()`
- Residential service countries through `Client::services()->residential()->countries()`
- Residential rotation intervals through `Client::services()->residential()->rotationIntervals()`
- Residential proxy request listing through `Client::services()->residential()->proxyRequests()`
- Residential proxy request lookup through `Client::services()->residential()->findProxyRequest()`
- Residential proxy request creation through `Client::services()->residential()->createProxyRequest()`
- Residential proxy request deletion through `Client::services()->residential()->deleteProxyRequest()`
- Residential proxy list lookup through `Client::services()->residential()->getProxyList()`

### Changed

- Organized endpoint requests and responses into hierarchical namespaces; this is a breaking namespace change without compatibility aliases
- Nullable `find()` lookups for Orders and Services when resources are not found
- Validation\Assert utility for reusable input validation
- Typed HealthResponse and HealthInterface
- Library HttpClientInterface and Symfony-backed HTTP client
- Generic response factory for API envelope and DTO hydration
- Retry handling for rate limits, server failures, and transport errors
- Rector for automated PHP refactoring
- PHPUnit configuration
- PHP 8.3 minimum language version and Rector PHP 8.3 configuration
- PHPstan and configuration
- PHP_CodeSniffer and coding standard definition
- Initial PHP library package structure
- Restricted automatic retries to read-only requests to prevent duplicated state changes
- Preserved transport failure causes and added bounded request timeouts
