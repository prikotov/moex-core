<?php

declare(strict_types=1);

namespace Moex\Skill\Service\Schedule;

use Moex\Skill\Service\Schedule\Dto\ScheduleResult;

interface ScheduleServiceInterface
{
    public function getTradingSchedule(?string $engine = null, ?string $market = null): ScheduleResult;
}
