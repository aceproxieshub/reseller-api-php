<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Endpoint;

use Aceproxies\ResellerApi\Exception\ApiException;
use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use Aceproxies\ResellerApi\Exception\TransportException;
use Aceproxies\ResellerApi\Request\Service\CreateIpReplacementRequest;
use Aceproxies\ResellerApi\Request\Service\CreateProlongationRequest;
use Aceproxies\ResellerApi\Request\Service\CreateWhitelistedIpRequest;
use Aceproxies\ResellerApi\Request\Service\UpdateCredentialsRequest;
use Aceproxies\ResellerApi\Request\Service\UpdateServiceRequest;
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
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpResponse;
use Aceproxies\ResellerApi\Response\Service\WhitelistedIpsResponse;
use InvalidArgumentException;

interface ServicesInterface
{
    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function list(?int $page = null, ?int $limit = null): ListResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function find(string $code): ?DetailResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getBandwidth(string $serviceCode): ?BandwidthResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getCredentials(string $serviceCode): ?CredentialsResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function updateCredentials(string $serviceCode, UpdateCredentialsRequest $request): CredentialsResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getWhitelistedIps(string $serviceCode): WhitelistedIpsResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function addWhitelistedIp(string $serviceCode, CreateWhitelistedIpRequest $request): WhitelistedIpResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function deleteWhitelistedIp(string $serviceCode, string $ip): void;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getIpReplacements(string $serviceCode): IpReplacementsResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function createIpReplacement(string $serviceCode, CreateIpReplacementRequest $request): IpReplacementResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getAvailableIpReplacements(string $serviceCode): IpReplacementCountResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getIpReplacementCount(string $serviceCode): IpReplacementCountResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getIpReplacementLocations(string $serviceCode): IpReplacementLocationsResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getProlongations(string $serviceCode): ProlongationsResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function createProlongation(string $serviceCode, CreateProlongationRequest $request): CreateProlongationResponse;

    /**
     * @throws ApiException
     * @throws InvalidResponseException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function update(string $code, UpdateServiceRequest $request): void;

    public function residential(): ResidentialInterface;
}
