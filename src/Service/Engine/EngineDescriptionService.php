<?php

declare(strict_types=1);

namespace Moex\Core\Service\Engine;

use JsonException;
use Moex\Core\Component\Moex\MoexIssComponentInterface;
use Moex\Core\Exception\InfrastructureException;
use Moex\Core\Service\Engine\Dto\EngineDescriptionRawDto;
use Override;

final class EngineDescriptionService implements EngineDescriptionServiceInterface
{
    public function __construct(
        private readonly MoexIssComponentInterface $moexComponent,
    ) {
    }

    #[Override]
    public function getEngineDescription(string $engine): EngineDescriptionRawDto
    {
        $content = $this->moexComponent->getContent(
            'engines/%s',
            [$engine],
            []
        );

        try {
            $data = json_decode($content ?? '', true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InfrastructureException(
                sprintf(
                    'Failed to decode MOEX ISS engine description response (engine: %s): %s',
                    $engine,
                    $e->getMessage()
                ),
                previous: $e
            );
        }

        if (!is_array($data)) {
            throw new InfrastructureException(sprintf(
                'MOEX ISS engine description response is not an array (engine: %s)',
                $engine
            ));
        }

        return new EngineDescriptionRawDto(
            engine: $this->findBlockRows($data, 'engine', $engine),
            timetable: $this->findBlockRows($data, 'timetable', $engine),
            dailytable: $this->findBlockRows($data, 'dailytable', $engine),
        );
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return list<array<string, null|scalar>>
     */
    private function findBlockRows(array $data, string $blockName, string $engine): array
    {
        foreach ($data as $element) {
            if (!is_array($element) || !array_key_exists($blockName, $element)) {
                continue;
            }

            $rows = $element[$blockName];

            if (!is_array($rows)) {
                throw new InfrastructureException(sprintf(
                    'Block "%s" is not a list of rows in MOEX ISS engine description response (engine: %s)',
                    $blockName,
                    $engine
                ));
            }

            /** @var list<array<string, null|scalar>> $rows */
            return $rows;
        }

        throw new InfrastructureException(sprintf(
            'Block "%s" not found in MOEX ISS engine description response (engine: %s)',
            $blockName,
            $engine
        ));
    }
}
