<?php

declare(strict_types=1);

namespace Moex\Core\Tests\Service\Engine;

use Moex\Core\Component\Moex\MoexIssComponentInterface;
use Moex\Core\Exception\InfrastructureException;
use Moex\Core\Service\Engine\Dto\EngineDescriptionRawDto;
use Moex\Core\Service\Engine\EngineDescriptionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
                'engine' => [
                    'columns' => ['NAME', 'TITLE', 'SHORTNAME'],
                    'data' => [
                        [
                            'NAME' => 'stock',
                            'TITLE' => 'Фондовый рынок и рынок депозитарных акций',
                            'SHORTNAME' => 'Фондовый рынок',
                        ],
                    ],
                ],
            ],
            1 => [
                'timetable' => [
                    'columns' => ['WEEKDAY', 'IS_WORK_DAY', 'START_TIME', 'STOP_TIME'],
                    'data' => [
                        [
                            'WEEKDAY' => 1,
                            'IS_WORK_DAY' => 1,
                            'START_TIME' => '10:00:00',
                            'STOP_TIME' => '18:40:00',
                        ],
                        [
                            'WEEKDAY' => 6,
                            'IS_WORK_DAY' => 0,
                            'START_TIME' => '00:00:00',
                            'STOP_TIME' => null,
                        ],
                    ],
                ],
            ],
            2 => [
                'dailytable' => [
                    'columns' => ['DATE', 'IS_WORK_DAY', 'START_TIME', 'STOP_TIME'],
                    'data' => [
                        [
                            'DATE' => '2024-01-15',
                            'IS_WORK_DAY' => 1,
                            'START_TIME' => '10:00:00',
                            'STOP_TIME' => '18:40:00',
                        ],
                        [
                            'DATE' => '2024-01-20',
                            'IS_WORK_DAY' => '0',
                            'START_TIME' => '',
                            'STOP_TIME' => null,
                        ],
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

        $this->assertInstanceOf(EngineDescriptionRawDto::class, $result);

        $this->assertSame([
            [
                'NAME' => 'stock',
                'TITLE' => 'Фондовый рынок и рынок депозитарных акций',
                'SHORTNAME' => 'Фондовый рынок',
            ],
        ], $result->engine);

        $this->assertSame([
            [
                'WEEKDAY' => 1,
                'IS_WORK_DAY' => 1,
                'START_TIME' => '10:00:00',
                'STOP_TIME' => '18:40:00',
            ],
            [
                'WEEKDAY' => 6,
                'IS_WORK_DAY' => 0,
                'START_TIME' => '00:00:00',
                'STOP_TIME' => null,
            ],
        ], $result->timetable);

        $this->assertSame([
            [
                'DATE' => '2024-01-15',
                'IS_WORK_DAY' => 1,
                'START_TIME' => '10:00:00',
                'STOP_TIME' => '18:40:00',
            ],
            [
                'DATE' => '2024-01-20',
                'IS_WORK_DAY' => '0',
                'START_TIME' => '',
                'STOP_TIME' => null,
            ],
        ], $result->dailytable);
    }

    public function testGetEngineDescriptionThrowsWhenDailytableBlockMissing(): void
    {
        $json = json_encode([
            0 => [
                'engine' => [
                    'data' => [
                        ['NAME' => 'stock', 'TITLE' => 'Фондовый рынок', 'SHORTNAME' => 'Фондовый рынок'],
                    ],
                ],
            ],
            1 => [
                'timetable' => [
                    'data' => [],
                ],
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
                'timetable' => [
                    'data' => [],
                ],
            ],
            1 => [
                'dailytable' => [
                    'data' => [],
                ],
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

    public function testGetEngineDescriptionReturnsEmptyDailytableRowsForEmptyData(): void
    {
        $json = json_encode([
            0 => [
                'engine' => [
                    'data' => [
                        ['NAME' => 'stock', 'TITLE' => 'Фондовый рынок', 'SHORTNAME' => 'Фондовый рынок'],
                    ],
                ],
            ],
            1 => [
                'timetable' => [
                    'data' => [],
                ],
            ],
            2 => [
                'dailytable' => [
                    'columns' => ['DATE', 'IS_WORK_DAY', 'START_TIME', 'STOP_TIME'],
                    'data' => [],
                ],
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

    public function testGetEngineDescriptionThrowsWhenBlockHasNoDataArray(): void
    {
        $json = json_encode([
            0 => [
                'engine' => [
                    'data' => [],
                ],
            ],
            1 => [
                'timetable' => [
                    'data' => [],
                ],
            ],
            2 => [
                'dailytable' => [
                    'columns' => ['DATE', 'IS_WORK_DAY', 'START_TIME', 'STOP_TIME'],
                ],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $this->expectException(InfrastructureException::class);
        $this->expectExceptionMessage('Block "dailytable" has no data array');

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
