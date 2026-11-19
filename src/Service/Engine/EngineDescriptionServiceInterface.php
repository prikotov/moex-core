<?php

declare(strict_types=1);

namespace Moex\Core\Service\Engine;

use Moex\Core\Service\Engine\Dto\EngineDescriptionRawDto;

interface EngineDescriptionServiceInterface
{
    public function getEngineDescription(string $engine): EngineDescriptionRawDto;
}
