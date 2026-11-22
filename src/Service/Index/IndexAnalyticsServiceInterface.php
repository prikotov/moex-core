<?php

declare(strict_types=1);

namespace Moex\Core\Service\Index;

use Moex\Core\Service\Index\Dto\IndexAnalyticsCompositionRawDto;
use Moex\Core\Service\Index\Dto\IndexAnalyticsListRawDto;

interface IndexAnalyticsServiceInterface
{
    /**
     * Список индексов: GET statistics/engines/stock/markets/index/analytics.json (ISS reference 146).
     * Возвращает сырой блок "indices" без парсинга.
     */
    public function getIndexAnalyticsList(): IndexAnalyticsListRawDto;

    /**
     * Состав индекса: GET statistics/engines/stock/markets/index/analytics/{index}.json (ISS reference 147).
     * Возвращает сырой блок "analytics" без парсинга.
     */
    public function getIndexAnalyticsComposition(string $index): IndexAnalyticsCompositionRawDto;
}
