<?php

declare(strict_types=1);

namespace Moex\Core\Service\Candle;

interface CandleServiceInterface
{
    public function getCandles(
        string $security,
        ?string $from = null,
        ?string $to = null,
        string $interval = '10'
    ): CandlesResult;
}
