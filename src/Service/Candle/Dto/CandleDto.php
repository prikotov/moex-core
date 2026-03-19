<?php

declare(strict_types=1);

namespace Moex\Core\Service\Candle\Dto;

use DateTimeImmutable;

final readonly class CandleDto
{
    public function __construct(
        public float $open,
        public float $close,
        public float $high,
        public float $low,
        public float $value,
        public int $volume,
        public DateTimeImmutable $begin,
        public DateTimeImmutable $end,
    ) {
    }
}
