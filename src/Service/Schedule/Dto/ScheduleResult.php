<?php

declare(strict_types=1);

namespace Moex\Core\Service\Schedule\Dto;

final readonly class ScheduleResult
{
    /**
     * @param TradingSessionDto[] $sessions
     */
    public function __construct(
        public bool $success,
        public array $sessions = [],
        public ?string $error = null,
    ) {
    }
}
