<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security;

use Moex\Core\Component\Moex\MoexIssComponentInterface;
use Moex\Core\Exception\InfrastructureExceptionInterface;
use Moex\Core\Service\Security\Dto\SecurityAggregateDto;
use Moex\Core\Service\Security\Dto\SecurityIndexDto;
use Moex\Core\Service\Security\Dto\SecuritySearchDto;
use Moex\Core\Service\Security\Dto\SecuritySpecificationDto;
use Moex\Core\Service\Security\Dto\SecurityTradeDataDto;
use Override;

final class SecurityService implements SecurityServiceInterface
{
    public function __construct(
        private readonly MoexIssComponentInterface $moexComponent,
    ) {
    }

    #[Override]
    public function search(string $query): SecuritySearchResult
    {
        try {
            $content = $this->moexComponent->getContent(
                'securities',
                [],
                [
                    'q' => $query,
                    'iss.only' => 'securities',
                ]
            );
        } catch (InfrastructureExceptionInterface $e) {
            return new SecuritySearchResult(
                success: false,
                error: $e->getMessage(),
            );
        }

        $data = json_decode($content ?? '', true);
        $securities = [];

        foreach ($data[1]['securities'] ?? [] as $item) {
            $securities[] = new SecuritySearchDto(
                secid: $item['secid'] ?? '',
                name: $item['name'] ?? '',
                shortName: $item['shortname'] ?? '',
                isin: $item['isin'] ?? '',
                typeName: $item['type'] ?? '',
                group: $item['group'] ?? '',
            );
        }

        return new SecuritySearchResult(
            success: true,
            securities: $securities,
        );
    }

    #[Override]
    public function getSpecification(string $security): SecuritySpecificationsResult
    {
        try {
            $content = $this->moexComponent->getContent(
                'securities/%s',
                [$security],
                ['iss.only' => 'description']
            );
        } catch (InfrastructureExceptionInterface $e) {
            return new SecuritySpecificationsResult(
                success: false,
                error: $e->getMessage(),
            );
        }

        $data = json_decode($content ?? '', true);
        $specifications = [];

        $description = $data[1]['description'] ?? [];
        $fields = $this->parseDescription($description);

        $specifications[] = new SecuritySpecificationDto(
            secid: $fields['SECID'] ?? '',
            name: $fields['NAME'] ?? '',
            shortName: $fields['SHORTNAME'] ?? '',
            isin: $fields['ISIN'] ?? '',
            regNumber: $fields['REGNUMBER'] ?? '',
            typeName: $fields['TYPENAME'] ?? '',
            group: $fields['GROUP'] ?? '',
            listLevel: (int)($fields['LISTLEVEL'] ?? 0),
            issueDate: $fields['ISSUEDATE'] ?? '',
            faceValue: $fields['FACEVALUE'] ?? null,
            faceUnit: $fields['FACEUNIT'] ?? null,
            issueSize: isset($fields['ISSUESIZE']) ? (int)$fields['ISSUESIZE'] : null,
        );

        return new SecuritySpecificationsResult(
            success: true,
            specifications: $specifications,
        );
    }

    #[Override]
    public function getTradeData(string $security): SecurityTradeDataResult
    {
        try {
            $content = $this->moexComponent->getContent(
                'engines/stock/markets/shares/boards/TQBR/securities/%s',
                [$security],
                [
                    'iss.only' => 'securities,marketdata',
                    'securities.columns' => 'BOARDID,BOARDNAME,SECID,SHORTNAME,' .
                        'SECNAME,PREVPRICE,PREVDATE,PREVLEGALCLOSEPRICE',
                    'marketdata.columns' => 'SECID,BOARDID,OPEN,LOW,HIGH,LAST,VALTODAY,TIME,SYSTIME',
                ]
            );
        } catch (InfrastructureExceptionInterface $e) {
            return new SecurityTradeDataResult(
                success: false,
                error: $e->getMessage(),
            );
        }

        $data = json_decode($content ?? '', true);
        $securities = [];
        $marketData = [];

        foreach ($data[1]['securities'] ?? [] as $item) {
            $securities[] = new SecurityTradeDataDto(
                secid: $item['SECID'] ?? '',
                shortName: $item['SHORTNAME'] ?? '',
                secName: $item['SECNAME'] ?? '',
                boardId: $item['BOARDID'] ?? '',
                boardName: $item['BOARDNAME'] ?? '',
                prevPrice: (float)($item['PREVPRICE'] ?? 0),
                prevDate: $item['PREVDATE'] ?? '',
            );
        }

        foreach ($data[1]['marketdata'] ?? [] as $item) {
            $marketData[] = new SecurityTradeDataDto(
                secid: $item['SECID'] ?? '',
                shortName: '',
                secName: '',
                boardId: $item['BOARDID'] ?? '',
                boardName: '',
                prevPrice: 0,
                prevDate: '',
                open: isset($item['OPEN']) ? (float)$item['OPEN'] : null,
                low: isset($item['LOW']) ? (float)$item['LOW'] : null,
                high: isset($item['HIGH']) ? (float)$item['HIGH'] : null,
                last: isset($item['LAST']) ? (float)$item['LAST'] : null,
                valToday: isset($item['VALTODAY']) ? (int)$item['VALTODAY'] : null,
                time: $item['TIME'] ?? null,
            );
        }

        if (empty($securities) && empty($marketData)) {
            return new SecurityTradeDataResult(
                success: false,
                error: 'Security not found on TQBR board',
            );
        }

        return new SecurityTradeDataResult(
            success: true,
            securities: $securities,
            marketData: $marketData,
        );
    }

    #[Override]
    public function getAggregates(string $security, ?string $date = null): SecurityAggregatesResult
    {
        $query = ['iss.only' => 'aggregates'];
        if ($date !== null) {
            $normalizedDate = $this->normalizeDate($date);
            if ($normalizedDate === null) {
                return new SecurityAggregatesResult(
                    success: false,
                    error: 'Invalid date format. Use YYYY-MM-DD.',
                );
            }
            $query['date'] = $normalizedDate;
        }

        try {
            $content = $this->moexComponent->getContent(
                'securities/%s/aggregates',
                [$security],
                $query
            );
        } catch (InfrastructureExceptionInterface $e) {
            return new SecurityAggregatesResult(
                success: false,
                error: $e->getMessage(),
            );
        }

        $data = json_decode($content ?? '', true);
        $aggregates = [];

        foreach ($data[1]['aggregates'] ?? [] as $item) {
            $aggregates[] = new SecurityAggregateDto(
                marketName: $item['market_name'] ?? '',
                marketTitle: $item['market_title'] ?? '',
                tradeDate: $item['tradedate'] ?? '',
                secid: $item['secid'] ?? '',
                value: (float)($item['value'] ?? 0),
                volume: (int)($item['volume'] ?? 0),
                numTrades: (int)($item['numtrades'] ?? 0),
                updatedAt: $item['updated_at'] ?? '',
            );
        }

        return new SecurityAggregatesResult(
            success: true,
            aggregates: $aggregates,
        );
    }

    #[Override]
    public function getIndices(string $security): SecurityIndicesResult
    {
        try {
            $content = $this->moexComponent->getContent(
                'securities/%s/indices',
                [$security],
                ['only_actual' => 1]
            );
        } catch (InfrastructureExceptionInterface $e) {
            return new SecurityIndicesResult(
                success: false,
                error: $e->getMessage(),
            );
        }

        $data = json_decode($content ?? '', true);
        $indices = [];

        foreach ($data[1]['indices'] ?? [] as $item) {
            $indices[] = new SecurityIndexDto(
                secid: $item['SECID'] ?? '',
                shortName: $item['SHORTNAME'] ?? '',
                from: $item['FROM'] ?? '',
                till: $item['TILL'] ?? null,
                currentValue: isset($item['CURRENTVALUE']) ? (float)$item['CURRENTVALUE'] : null,
                lastChangePrc: isset($item['LASTCHANGEPRC']) ? (float)$item['LASTCHANGEPRC'] : null,
                lastChange: isset($item['LASTCHANGE']) ? (float)$item['LASTCHANGE'] : null,
            );
        }

        return new SecurityIndicesResult(
            success: true,
            indices: $indices,
        );
    }

    private function normalizeDate(string $date): ?string
    {
        $dt = date_create_immutable($date);
        return $dt === false ? null : $dt->format('Y-m-d');
    }

    /**
     * @param array<array{name: string, value: string}> $description
     * @return array<string, string>
     */
    private function parseDescription(array $description): array
    {
        $fields = [];
        foreach ($description as $item) {
            $fields[$item['name']] = $item['value'];
        }
        return $fields;
    }
}
