<?php

declare(strict_types=1);

namespace Moex\Core\Service\Schedule\Dto;

final readonly class TradingSessionDto
{
    public function __construct(
        public string $engine,
        public string $market,
        public string $title,
        public ?string $startTime = null,
        public ?string $endTime = null,
        public ?string $auctionStart = null,
        public ?string $auctionEnd = null,
        public ?string $eveningStart = null,
        public ?string $eveningEnd = null,
        public ?string $clearingStart = null,
        public ?string $clearingEnd = null,
    ) {
    }
}
