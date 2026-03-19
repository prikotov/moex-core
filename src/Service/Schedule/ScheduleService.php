<?php

declare(strict_types=1);

namespace Moex\Skill\Service\Schedule;

use Moex\Skill\Component\Moex\MoexIssComponentInterface;
use Moex\Skill\Exception\InfrastructureExceptionInterface;
use Moex\Skill\Service\Schedule\Dto\ScheduleResult;
use Moex\Skill\Service\Schedule\Dto\TradingSessionDto;
use Override;

final class ScheduleService implements ScheduleServiceInterface
{
    private const MARKET_NAMES = [
        'shares' => 'Акции',
        'bonds' => 'Облигации',
        'futures' => 'Фьючерсы',
        'currency' => 'Валюта',
        'commodity' => 'Товары',
    ];

    public function __construct(
        private readonly MoexIssComponentInterface $moexComponent,
    ) {
    }

    #[Override]
    public function getTradingSchedule(?string $engine = null, ?string $market = null): ScheduleResult
    {
        $query = ['iss.only' => 'sessions'];

        $url = 'engines';
        $urlData = [];

        if ($engine !== null) {
            $url = 'engines/%s';
            $urlData = [$engine];

            if ($market !== null) {
                $url = 'engines/%s/markets/%s';
                $urlData = [$engine, $market];
            }
        }

        try {
            $content = $this->moexComponent->getContent(
                $url . '/trading',
                $urlData,
                $query
            );
        } catch (InfrastructureExceptionInterface $e) {
            return new ScheduleResult(
                success: false,
                error: $e->getMessage(),
            );
        }

        $data = json_decode($content ?? '', true);
        $sessions = [];

        foreach ($data[1]['sessions'] ?? [] as $item) {
            $marketKey = $item['market'] ?? '';
            $sessions[] = new TradingSessionDto(
                engine: $item['engine'] ?? '',
                market: $marketKey,
                title: self::MARKET_NAMES[$marketKey] ?? $marketKey,
                startTime: $item['begintime'] ?? null,
                endTime: $item['endtime'] ?? null,
                auctionStart: $item['openingtime'] ?? null,
                auctionEnd: $item['closingtime'] ?? null,
                eveningStart: $item['eveningbegintime'] ?? null,
                eveningEnd: $item['eveningendtime'] ?? null,
                clearingStart: $item['clearingbegintime'] ?? null,
                clearingEnd: $item['clearingendtime'] ?? null,
            );
        }

        return new ScheduleResult(
            success: true,
            sessions: $sessions,
        );
    }
}
