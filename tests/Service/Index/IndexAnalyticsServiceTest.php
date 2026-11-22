<?php

declare(strict_types=1);

namespace Moex\Core\Tests\Service\Index;

use Moex\Core\Component\Moex\MoexIssComponentInterface;
use Moex\Core\Exception\InfrastructureException;
use Moex\Core\Service\Index\IndexAnalyticsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Форма payload повторяет живой ответ ISS на
 * statistics/engines/stock/markets/index/analytics.json и .../analytics/IMOEX.json
 * с iss.meta=off&iss.json=extended (компонент добавляет оба параметра сам):
 * второй элемент массива содержит блок indices/analytics как список ассоциативных строк.
 * Без iss.json=extended живой ISS отдаёт блок как {"columns": [...], "data": [[...], ...]}.
 */
final class IndexAnalyticsServiceTest extends TestCase
{
    private MoexIssComponentInterface&MockObject $moexComponent;
    private IndexAnalyticsService $service;

    protected function setUp(): void
    {
        $this->moexComponent = $this->createMock(MoexIssComponentInterface::class);
        $this->service = new IndexAnalyticsService($this->moexComponent);
    }

    public function testGetIndexAnalyticsListReturnsRawBlocksWithoutCasts(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'indices' => [
                    [
                        'indexid' => 'IMOEX',
                        'shortname' => 'Индекс МосБиржи',
                        'from' => '2001-01-03',
                        'till' => '2026-11-20',
                    ],
                    [
                        'indexid' => 'MOEX10',
                        'shortname' => 'Индекс МосБиржи 10',
                        'from' => '2008-05-27',
                        'till' => '2026-11-20',
                    ],
                ],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->with('statistics/engines/stock/markets/index/analytics', [], [])
            ->willReturn($json);

        $result = $this->service->getIndexAnalyticsList();

        $this->assertSame([
            [
                'indexid' => 'IMOEX',
                'shortname' => 'Индекс МосБиржи',
                'from' => '2001-01-03',
                'till' => '2026-11-20',
            ],
            [
                'indexid' => 'MOEX10',
                'shortname' => 'Индекс МосБиржи 10',
                'from' => '2008-05-27',
                'till' => '2026-11-20',
            ],
        ], $result->indices);
    }

    public function testGetIndexAnalyticsCompositionReturnsRawBlocksWithoutCasts(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'analytics' => [
                    [
                        'indexid' => 'IMOEX',
                        'tradedate' => '2026-11-20',
                        'ticker' => 'AFKS',
                        'shortnames' => 'Система ао',
                        'secids' => 'AFKS',
                        'weight' => 0.26,
                        'tradingsession' => 3,
                        'trade_session_date' => '2026-11-20',
                    ],
                    [
                        'indexid' => 'IMOEX',
                        'tradedate' => '2026-11-20',
                        'ticker' => 'AFLT',
                        'shortnames' => 'Аэрофлот',
                        'secids' => 'AFLT',
                        'weight' => 0.49,
                        'tradingsession' => 3,
                        'trade_session_date' => '2026-11-20',
                    ],
                ],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->with('statistics/engines/stock/markets/index/analytics/%s', ['IMOEX'], [])
            ->willReturn($json);

        $result = $this->service->getIndexAnalyticsComposition('IMOEX');

        $this->assertSame([
            [
                'indexid' => 'IMOEX',
                'tradedate' => '2026-11-20',
                'ticker' => 'AFKS',
                'shortnames' => 'Система ао',
                'secids' => 'AFKS',
                'weight' => 0.26,
                'tradingsession' => 3,
                'trade_session_date' => '2026-11-20',
            ],
            [
                'indexid' => 'IMOEX',
                'tradedate' => '2026-11-20',
                'ticker' => 'AFLT',
                'shortnames' => 'Аэрофлот',
                'secids' => 'AFLT',
                'weight' => 0.49,
                'tradingsession' => 3,
                'trade_session_date' => '2026-11-20',
            ],
        ], $result->analytics);
    }

    public function testGetIndexAnalyticsListReturnsEmptyRowsForEmptyBlock(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'indices' => [],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $result = $this->service->getIndexAnalyticsList();

        $this->assertSame([], $result->indices);
    }

    public function testGetIndexAnalyticsListThrowsWhenIndicesBlockMissing(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'analytics' => [],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Block "indices" not found');

        $this->service->getIndexAnalyticsList();
    }

    public function testGetIndexAnalyticsListThrowsWhenIndicesBlockIsNotAListOfRows(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'indices' => 'not-a-list',
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Block "indices" is not a list of rows');

        $this->service->getIndexAnalyticsList();
    }

    public function testGetIndexAnalyticsListThrowsOnInvalidJson(): void
    {
        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{not-json');

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Failed to decode MOEX ISS index analytics list response');

        $this->service->getIndexAnalyticsList();
    }

    public function testGetIndexAnalyticsListRethrowsComponentException(): void
    {
        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willThrowException(new InfrastructureException('Failed to get data from MOEX ISS'));

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Failed to get data from MOEX ISS');

        $this->service->getIndexAnalyticsList();
    }

    public function testGetIndexAnalyticsCompositionThrowsWhenAnalyticsBlockMissing(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'indices' => [],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Block "analytics" not found in MOEX ISS index analytics response (index: IMOEX)');

        $this->service->getIndexAnalyticsComposition('IMOEX');
    }

    public function testGetIndexAnalyticsCompositionThrowsWhenAnalyticsBlockIsNotAListOfRows(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'analytics' => 42,
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Block "analytics" is not a list of rows in MOEX ISS index analytics response (index: IMOEX)');

        $this->service->getIndexAnalyticsComposition('IMOEX');
    }

    public function testGetIndexAnalyticsCompositionThrowsOnInvalidJson(): void
    {
        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('');

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Failed to decode MOEX ISS index analytics composition response (index: IMOEX)');

        $this->service->getIndexAnalyticsComposition('IMOEX');
    }

    public function testGetIndexAnalyticsCompositionRethrowsComponentException(): void
    {
        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willThrowException(new InfrastructureException('Failed to get data from MOEX ISS'));

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Failed to get data from MOEX ISS');

        $this->service->getIndexAnalyticsComposition('IMOEX');
    }
}
