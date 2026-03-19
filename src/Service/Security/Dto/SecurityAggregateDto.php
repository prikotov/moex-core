<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security\Dto;

final readonly class SecurityAggregateDto
{
    public function __construct(
        public string $marketName,
        public string $marketTitle,
        public string $tradeDate,
        public string $secid,
        public float $value,
        public int $volume,
        public int $numTrades,
        public string $updatedAt,
    ) {
    }
}
