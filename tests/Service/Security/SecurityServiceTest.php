<?php

declare(strict_types=1);

namespace Moex\Core\Tests\Service\Security;

use Moex\Core\Component\Moex\MoexIssComponentInterface;
use Moex\Core\Exception\InfrastructureException;
use Moex\Core\Service\Security\SecurityService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SecurityServiceTest extends TestCase
{
    private MoexIssComponentInterface&MockObject $moexComponent;
    private SecurityService $service;

    protected function setUp(): void
    {
        $this->moexComponent = $this->createMock(MoexIssComponentInterface::class);
        $this->service = new SecurityService($this->moexComponent);
    }

    public function testGetSpecificationReturnsSpecificationDto(): void
    {
        $json = json_encode([
            1 => [
                'description' => [
                    ['name' => 'SECID', 'value' => 'SBER'],
                    ['name' => 'NAME', 'value' => 'Сбербанк России ПАО'],
                    ['name' => 'SHORTNAME', 'value' => 'СберБанк'],
                    ['name' => 'ISIN', 'value' => 'RU0009029540'],
                    ['name' => 'REGNUMBER', 'value' => '10301481B'],
                    ['name' => 'TYPENAME', 'value' => 'Акция'],
                    ['name' => 'GROUP', 'value' => 'EQ_SHAR'],
                    ['name' => 'LISTLEVEL', 'value' => '1'],
                    ['name' => 'ISSUEDATE', 'value' => '2011-04-26'],
                    ['name' => 'FACEVALUE', 'value' => '3'],
                    ['name' => 'FACEUNIT', 'value' => 'RUB'],
                    ['name' => 'ISSUESIZE', 'value' => '21574696000'],
                ],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->with('securities/%s', ['SBER'], ['iss.only' => 'description'])
            ->willReturn($json);

        $result = $this->service->getSpecification('SBER');

        $this->assertTrue($result->success);
        $this->assertNull($result->error);
        $this->assertCount(1, $result->specifications);

        $spec = $result->specifications[0];
        $this->assertSame('SBER', $spec->secid);
        $this->assertSame('Сбербанк России ПАО', $spec->name);
        $this->assertSame('СберБанк', $spec->shortName);
        $this->assertSame('RU0009029540', $spec->isin);
        $this->assertSame('10301481B', $spec->regNumber);
        $this->assertSame('Акция', $spec->typeName);
        $this->assertSame('EQ_SHAR', $spec->group);
        $this->assertSame(1, $spec->listLevel);
        $this->assertSame('2011-04-26', $spec->issueDate);
        $this->assertSame('3', $spec->faceValue);
        $this->assertSame('RUB', $spec->faceUnit);
        $this->assertSame(21574696000, $spec->issueSize);
    }

    public function testGetSpecificationReturnsErrorOnException(): void
    {
        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willThrowException(new InfrastructureException('Network error'));

        $result = $this->service->getSpecification('INVALID');

        $this->assertFalse($result->success);
        $this->assertSame('Network error', $result->error);
        $this->assertEmpty($result->specifications);
    }

    public function testGetTradeDataReturnsTradeDataDto(): void
    {
        $json = json_encode([
            1 => [
                'securities' => [
                    [
                        'BOARDID' => 'TQBR',
                        'BOARDNAME' => 'Т+: Акции и ДР',
                        'SECID' => 'SBER',
                        'SHORTNAME' => 'СберБанк',
                        'SECNAME' => 'Сбербанк России ПАО',
                        'PREVPRICE' => '267.45',
                        'PREVDATE' => '2024-01-15',
                    ],
                ],
                'marketdata' => [
                    [
                        'SECID' => 'SBER',
                        'BOARDID' => 'TQBR',
                        'OPEN' => '268.00',
                        'LOW' => '265.50',
                        'HIGH' => '270.00',
                        'LAST' => '269.00',
                        'VALTODAY' => '15000000000',
                        'TIME' => '18:39:59',
                    ],
                ],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $result = $this->service->getTradeData('SBER');

        $this->assertTrue($result->success);
        $this->assertCount(1, $result->securities);
        $this->assertCount(1, $result->marketData);

        $security = $result->securities[0];
        $this->assertSame('SBER', $security->secid);
        $this->assertSame('СберБанк', $security->shortName);
        $this->assertSame('Сбербанк России ПАО', $security->secName);
        $this->assertSame('TQBR', $security->boardId);
        $this->assertSame(267.45, $security->prevPrice);

        $market = $result->marketData[0];
        $this->assertSame('SBER', $market->secid);
        $this->assertSame(268.0, $market->open);
        $this->assertSame(265.5, $market->low);
        $this->assertSame(270.0, $market->high);
        $this->assertSame(269.0, $market->last);
        $this->assertSame(15000000000, $market->valToday);
    }

    public function testGetAggregatesReturnsAggregateDto(): void
    {
        $json = json_encode([
            1 => [
                'aggregates' => [
                    [
                        'market_name' => 'shares',
                        'market_title' => 'Акции',
                        'tradedate' => '2024-01-15',
                        'secid' => 'SBER',
                        'value' => '15000000000.50',
                        'volume' => '55000000',
                        'numtrades' => '120000',
                        'updated_at' => '2024-01-15 18:40:00',
                    ],
                ],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $result = $this->service->getAggregates('SBER');

        $this->assertTrue($result->success);
        $this->assertCount(1, $result->aggregates);

        $aggregate = $result->aggregates[0];
        $this->assertSame('shares', $aggregate->marketName);
        $this->assertSame('Акции', $aggregate->marketTitle);
        $this->assertSame('2024-01-15', $aggregate->tradeDate);
        $this->assertSame('SBER', $aggregate->secid);
        $this->assertSame(15000000000.50, $aggregate->value);
        $this->assertSame(55000000, $aggregate->volume);
        $this->assertSame(120000, $aggregate->numTrades);
    }

    public function testGetAggregatesReturnsErrorOnInvalidDate(): void
    {
        $result = $this->service->getAggregates('SBER', 'invalid-date');

        $this->assertFalse($result->success);
        $this->assertSame('Invalid date format. Use YYYY-MM-DD.', $result->error);
    }

    public function testGetIndicesReturnsIndexDto(): void
    {
        $json = json_encode([
            1 => [
                'indices' => [
                    [
                        'SECID' => 'MOEXBC',
                        'SHORTNAME' => 'МосБиржа БрокКлир',
                        'FROM' => '2020-01-01',
                        'TILL' => null,
                        'CURRENTVALUE' => '4500.50',
                        'LASTCHANGEPRC' => '0.5',
                        'LASTCHANGE' => '22.50',
                    ],
                ],
            ],
        ]);

        $this->moexComponent
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($json);

        $result = $this->service->getIndices('SBER');

        $this->assertTrue($result->success);
        $this->assertCount(1, $result->indices);

        $index = $result->indices[0];
        $this->assertSame('MOEXBC', $index->secid);
        $this->assertSame('МосБиржа БрокКлир', $index->shortName);
        $this->assertSame('2020-01-01', $index->from);
        $this->assertNull($index->till);
        $this->assertSame(4500.50, $index->currentValue);
        $this->assertSame(0.5, $index->lastChangePrc);
        $this->assertSame(22.50, $index->lastChange);
    }
}
