<?php

declare(strict_types=1);

namespace Moex\Core\Service\Index;

use JsonException;
use Moex\Core\Component\Moex\MoexIssComponentInterface;
use Moex\Core\Exception\InfrastructureException;
use Moex\Core\Service\Index\Dto\IndexAnalyticsCompositionRawDto;
use Moex\Core\Service\Index\Dto\IndexAnalyticsListRawDto;
use Override;

final class IndexAnalyticsService implements IndexAnalyticsServiceInterface
{
    private const LIST_PATH = 'statistics/engines/stock/markets/index/analytics';
    private const COMPOSITION_PATH = 'statistics/engines/stock/markets/index/analytics/%s';

    public function __construct(
        private readonly MoexIssComponentInterface $moexComponent,
    ) {
    }

    #[Override]
    public function getIndexAnalyticsList(): IndexAnalyticsListRawDto
    {
        $content = $this->moexComponent->getContent(
            self::LIST_PATH,
            [],
            []
        );

        try {
            $data = json_decode($content ?? '', true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InfrastructureException(
                sprintf(
                    'Failed to decode MOEX ISS index analytics list response: %s',
                    $e->getMessage()
                ),
                previous: $e
            );
        }

        if (!is_array($data)) {
            throw new InfrastructureException(
                'MOEX ISS index analytics list response is not an array'
            );
        }

        return new IndexAnalyticsListRawDto(
            indices: $this->findBlockRows($data, 'indices'),
        );
    }

    #[Override]
    public function getIndexAnalyticsComposition(string $index): IndexAnalyticsCompositionRawDto
    {
        $content = $this->moexComponent->getContent(
            self::COMPOSITION_PATH,
            [$index],
            []
        );

        try {
            $data = json_decode($content ?? '', true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InfrastructureException(
                sprintf(
                    'Failed to decode MOEX ISS index analytics composition response (index: %s): %s',
                    $index,
                    $e->getMessage()
                ),
                previous: $e
            );
        }

        if (!is_array($data)) {
            throw new InfrastructureException(sprintf(
                'MOEX ISS index analytics composition response is not an array (index: %s)',
                $index
            ));
        }

        return new IndexAnalyticsCompositionRawDto(
            analytics: $this->findBlockRows($data, 'analytics', $index),
        );
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return list<array<string, null|scalar>>
     */
    private function findBlockRows(array $data, string $blockName, string $index = ''): array
    {
        $indexContext = $index === '' ? '' : sprintf(' (index: %s)', $index);

        foreach ($data as $element) {
            if (!is_array($element) || !array_key_exists($blockName, $element)) {
                continue;
            }

            $rows = $element[$blockName];

            if (!is_array($rows)) {
                throw new InfrastructureException(sprintf(
                    'Block "%s" is not a list of rows in MOEX ISS index analytics response%s',
                    $blockName,
                    $indexContext
                ));
            }

            /** @var list<array<string, null|scalar>> $rows */
            return $rows;
        }

        throw new InfrastructureException(sprintf(
            'Block "%s" not found in MOEX ISS index analytics response%s',
            $blockName,
            $indexContext
        ));
    }
}
