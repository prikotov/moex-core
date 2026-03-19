<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security\Dto;

final readonly class SecurityTradeDataDto
{
    public function __construct(
        public string $secid,
        public string $shortName,
        public string $secName,
        public string $boardId,
        public string $boardName,
        public float $prevPrice,
        public string $prevDate,
        public ?float $open = null,
        public ?float $low = null,
        public ?float $high = null,
        public ?float $last = null,
        public ?int $valToday = null,
        public ?string $time = null,
    ) {
    }
}
