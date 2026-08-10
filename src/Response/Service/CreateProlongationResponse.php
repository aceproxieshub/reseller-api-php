<?php

declare(strict_types=1);

namespace Aceproxies\ResellerApi\Response\Service;

use DateTimeImmutable;

final readonly class CreateProlongationResponse
{
    public DateTimeImmutable $newExpirationDate;

    public function __construct(
        public string $durationId,
        string $newExpirationDate,
        public int $quantity,
        public string $status,
    ) {
        $this->newExpirationDate = new DateTimeImmutable($newExpirationDate);
    }
}
