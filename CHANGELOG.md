# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- API version method Client::getApiVersion()
- Health endpoint support through Client::health()->getHealth()
- Balance endpoint support with bearer authorization headers
- Orders list, creation, and details endpoint support
- Products list and types endpoint support
- Services list endpoint support
- Service detail lookup through `Client::services()->find()`
- Service updates through `Client::services()->update()`
- Residential service countries through `Client::services()->residential()->countries()`
- Residential rotation intervals through `Client::services()->residential()->rotationIntervals()`

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
