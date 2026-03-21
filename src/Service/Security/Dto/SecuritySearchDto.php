<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security\Dto;

final readonly class SecuritySearchDto
{
    public function __construct(
        public string $secid,
        public string $name,
        public string $shortName,
        public string $isin,
        public string $typeName,
        public string $group,
    ) {
    }
}
