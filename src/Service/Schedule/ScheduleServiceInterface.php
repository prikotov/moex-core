<?php

declare(strict_types=1);

namespace Moex\Core\Service\Schedule;

use Moex\Core\Service\Schedule\Dto\ScheduleResult;

interface ScheduleServiceInterface
{
    public function getTradingSchedule(?string $engine = null, ?string $market = null): ScheduleResult;
}
