<?php

declare(strict_types=1);

namespace Moex\Core\Service\Candle;

use DateTimeImmutable;
use Moex\Core\Component\Moex\MoexIssComponentInterface;
use Moex\Core\Exception\InfrastructureExceptionInterface;
use Moex\Core\Service\Candle\Dto\CandleDto;
use Override;

final class CandleService implements CandleServiceInterface
{
    public function __construct(
        private readonly MoexIssComponentInterface $moexComponent,
    ) {
    }

    #[Override]
    public function getCandles(
        string $security,
        ?string $from = null,
        ?string $to = null,
        string $interval = '10'
    ): CandlesResult {
        $query = [
            'iss.only' => 'candles',
            'interval' => $interval,
        ];

        if ($from !== null) {
            $query['from'] = $from;
        }
        if ($to !== null) {
            $query['till'] = $to;
        }

        try {
            $content = $this->moexComponent->getContent(
                'engines/stock/markets/shares/boards/TQBR/securities/%s/candles',
                [$security],
                $query
            );
        } catch (InfrastructureExceptionInterface $e) {
            return new CandlesResult(
                success: false,
                error: $e->getMessage(),
            );
        }

        $data = json_decode($content ?? '', true);
        $candles = [];

        $columns = $data[1]['candles']['columns'] ?? [];
        $candleData = $data[1]['candles']['data'] ?? [];

        $columnMap = array_flip($columns);

        foreach ($candleData as $item) {
            $candles[] = new CandleDto(
                open: (float)$item[$columnMap['open']],
                close: (float)$item[$columnMap['close']],
                high: (float)$item[$columnMap['high']],
                low: (float)$item[$columnMap['low']],
                value: (float)$item[$columnMap['value']],
                volume: (int)$item[$columnMap['volume']],
                begin: new DateTimeImmutable($item[$columnMap['begin']]),
                end: new DateTimeImmutable($item[$columnMap['end']]),
            );
        }

        return new CandlesResult(
            success: true,
            candles: $candles,
        );
    }
}
