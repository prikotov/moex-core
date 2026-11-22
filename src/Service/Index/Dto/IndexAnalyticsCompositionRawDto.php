<?php

declare(strict_types=1);

namespace Moex\Core\Service\Index\Dto;

/**
 * Сырой блок "analytics" из ответа ISS statistics/engines/stock/markets/index/analytics/{index}.json.
 *
 * Живой формат ISS (проверено 2026-11, пример IMOEX): без iss.json=extended блок отдаётся как
 * {"columns": ["indexid", "tradedate", "ticker", "shortnames", "secids", "weight",
 * "tradingsession", "trade_session_date"], "data": [["IMOEX", ..., "AFKS", ..., 0.26, 3, ...], ...]};
 * компонент MoexIssComponent всегда запрашивает iss.json=extended&iss.meta=off, при этом
 * блок приходит как список ассоциативных строк:
 * [{"indexid": "IMOEX", "tradedate": "...", "ticker": "AFKS", ..., "weight": 0.26, ...}, ...].
 *
 * Сервис возвращает блок в extended-виде как есть, без кастов; позиционный разбор
 * columns/data делает consuming-проект (stocks2).
 */
final readonly class IndexAnalyticsCompositionRawDto
{
    /**
     * @param list<array<string, null|scalar>> $analytics
     */
    public function __construct(
        public array $analytics,
    ) {
    }
}
