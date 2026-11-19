<?php

declare(strict_types=1);

namespace Moex\Core\Service\Engine\Dto;

final readonly class EngineDescriptionRawDto
{
    /**
     * @param list<array<string, null|scalar>> $engine
     * @param list<array<string, null|scalar>> $timetable
     * @param list<array<string, null|scalar>> $dailytable
     */
    public function __construct(
        public array $engine,
        public array $timetable,
        public array $dailytable,
    ) {
    }
}
