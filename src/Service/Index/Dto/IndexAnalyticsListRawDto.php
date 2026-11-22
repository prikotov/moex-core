<?php

declare(strict_types=1);

namespace Moex\Core\Service\Index\Dto;

/**
 * Сырой блок "indices" из ответа ISS statistics/engines/stock/markets/index/analytics.json.
 *
 * Живой формат ISS (проверено 2026-11): без iss.json=extended блок отдаётся как
 * {"columns": ["indexid", "shortname", "from", "till"], "data": [["IMOEX", ...], ...]};
 * компонент MoexIssComponent всегда запрашивает iss.json=extended&iss.meta=off, при этом
 * блок приходит как список ассоциативных строк:
 * [{"indexid": "IMOEX", "shortname": "Индекс МосБиржи", "from": "...", "till": "..."}, ...].
 *
 * Сервис возвращает блок в extended-виде как есть, без кастов; позиционный разбор
 * columns/data делает consuming-проект (stocks2).
 */
final readonly class IndexAnalyticsListRawDto
{
    /**
     * @param list<array<string, null|scalar>> $indices
     */
    public function __construct(
        public array $indices,
    ) {
    }
}
