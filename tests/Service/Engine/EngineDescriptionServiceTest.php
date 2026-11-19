<?php

declare(strict_types=1);

namespace Moex\Core\Tests\Service\Engine;

use Moex\Core\Component\Moex\MoexIssComponentInterface;
use Moex\Core\Exception\InfrastructureException;
use Moex\Core\Service\Engine\EngineDescriptionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Форма payload повторяет живой ответ ISS на engines/stock.json?iss.meta=off&iss.json=extended:
 * второй элемент массива содержит блоки engine/timetable/dailytable как списки ассоциативных строк.
 */
final class EngineDescriptionServiceTest extends TestCase
{
    private MoexIssComponentInterface&MockObject $moexComponent;
    private EngineDescriptionService $service;

    protected function setUp(): void
    {
        $this->moexComponent = $this->createMock(MoexIssComponentInterface::class);
        $this->service = new EngineDescriptionService($this->moexComponent);
    }

    public function testGetEngineDescriptionReturnsRawBlocksWithoutCasts(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'engine' => [
                    [
                        'NAME' => 'stock',
                        'title' => 'Фондовый рынок и рынок депозитов',
                        'short_title' => 'ФБ',
                    ],
                ],
                'timetable' => [
                    [
                        'week_day' => 1,
                        'is_work_day' => 1,
                        'start_time' => '10:00:00',
                        'stop_time' => '18:40:00',
                    ],
                    [
                        'week_day' => 6,
                        'is_work_day' => 0,
                        'start_time' => '00:00:00',
                        'stop_time' => null,
                    ],
                ],
                'dailytable' => [
                    [
                        'date' => '2024-01-15',
                        'is_work_day' => 1,
                        'start_time' => '10:00:00',
                        'stop_time' => '18:40:00',
                    ],
                    [
                        'date' => '2024-01-20',
                        'is_work_day' => '0',
                        'start_time' => '',
                        'stop_time' => null,
                    ],
                ],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->with('engines/%s', ['stock'], [])
            ->willReturn($json);

        $result = $this->service->getEngineDescription('stock');

        $this->assertSame([
            [
                'NAME' => 'stock',
                'title' => 'Фондовый рынок и рынок депозитов',
                'short_title' => 'ФБ',
            ],
        ], $result->engine);

        $this->assertSame([
            [
                'week_day' => 1,
                'is_work_day' => 1,
                'start_time' => '10:00:00',
                'stop_time' => '18:40:00',
            ],
            [
                'week_day' => 6,
                'is_work_day' => 0,
                'start_time' => '00:00:00',
                'stop_time' => null,
            ],
        ], $result->timetable);

        $this->assertSame([
            [
                'date' => '2024-01-15',
                'is_work_day' => 1,
                'start_time' => '10:00:00',
                'stop_time' => '18:40:00',
            ],
            [
                'date' => '2024-01-20',
                'is_work_day' => '0',
                'start_time' => '',
                'stop_time' => null,
            ],
        ], $result->dailytable);
    }

    public function testGetEngineDescriptionThrowsWhenDailytableBlockMissing(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'engine' => [
                    ['NAME' => 'stock', 'title' => 'Фондовый рынок', 'short_title' => 'ФБ'],
                ],
                'timetable' => [],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->with('engines/%s', ['stock'], [])
            ->willReturn($json);

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Block "dailytable" not found');

        $this->service->getEngineDescription('stock');
    }

    public function testGetEngineDescriptionThrowsWhenEngineBlockMissing(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'timetable' => [],
                'dailytable' => [],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Block "engine" not found');

        $this->service->getEngineDescription('stock');
    }

    public function testGetEngineDescriptionThrowsWhenTimetableBlockMissing(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'engine' => [
                    ['NAME' => 'stock', 'title' => 'Фондовый рынок', 'short_title' => 'ФБ'],
                ],
                'dailytable' => [],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Block "timetable" not found');

        $this->service->getEngineDescription('stock');
    }

    public function testGetEngineDescriptionReturnsEmptyDailytableRowsForEmptyList(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'engine' => [
                    ['NAME' => 'stock', 'title' => 'Фондовый рынок', 'short_title' => 'ФБ'],
                ],
                'timetable' => [],
                'dailytable' => [],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $result = $this->service->getEngineDescription('stock');

        $this->assertSame([], $result->dailytable);
        $this->assertCount(1, $result->engine);
        $this->assertSame([], $result->timetable);
    }

    public function testGetEngineDescriptionThrowsWhenBlockIsNotAListOfRows(): void
    {
        $json = json_encode([
            0 => [
                'charsetinfo' => ['name' => 'utf-8'],
            ],
            1 => [
                'engine' => [],
                'timetable' => [],
                'dailytable' => 'not-a-list',
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Block "dailytable" is not a list of rows');

        $this->service->getEngineDescription('stock');
    }

    public function testGetEngineDescriptionThrowsOnInvalidJson(): void
    {
        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{not-json');

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Failed to decode MOEX ISS engine description response');

        $this->service->getEngineDescription('stock');
    }

    public function testGetEngineDescriptionRethrowsComponentException(): void
    {
        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willThrowException(new InfrastructureException('Failed to get data from MOEX ISS'));

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Failed to get data from MOEX ISS');

        $this->service->getEngineDescription('stock');
    }
}
