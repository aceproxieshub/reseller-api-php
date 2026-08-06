<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response;

use Aceproxies\ResellerApi\Exception\InvalidResponseException;
use ReflectionClass;
use Throwable;

final readonly class ResponseFactory
{
    /**
     * @template T of object
     * @param class-string<T> $responseClass
     * @return T
     */
    public function create(string $body, string $responseClass, int $statusCode): object
    {
        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            $data = is_array($decoded) ? ($decoded['data'] ?? null) : null;

            if (!is_array($data)) {
                throw new InvalidResponseException($statusCode, $body);
            }

            $reflection = new ReflectionClass($responseClass);
            $constructor = $reflection->getConstructor();
            $arguments = [];

            if ($constructor !== null) {
                foreach ($constructor->getParameters() as $parameter) {
                    $name = $parameter->getName();

                    if (array_key_exists($name, $data)) {
                        $arguments[] = $data[$name];
                    } elseif ($parameter->isDefaultValueAvailable()) {
                        $arguments[] = $parameter->getDefaultValue();
                    } else {
                        throw new InvalidResponseException($statusCode, $body);
                    }
                }
            }

            return $reflection->newInstanceArgs($arguments);
        } catch (InvalidResponseException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new InvalidResponseException($statusCode, $body, $exception);
        }
    }
}
