<?php

declare(strict_types=1);

namespace Moex\Core\Service\Candle;

use Moex\Core\Service\Candle\Dto\CandleDto;

final readonly class CandlesResult
{
    /**
     * @param array<CandleDto> $candles
     */
    public function __construct(
        public bool $success,
        public ?string $error = null,
        public array $candles = [],
    ) {
    }
}
