<?php

declare(strict_types=1);

namespace Moex\Skill\Service\Security\Dto;

final readonly class SecuritySpecificationDto
{
    public function __construct(
        public string $secid,
        public string $name,
        public string $shortName,
        public string $isin,
        public string $regNumber,
        public string $typeName,
        public string $group,
        public int $listLevel,
        public string $issueDate,
        public ?string $faceValue = null,
        public ?string $faceUnit = null,
        public ?int $issueSize = null,
    ) {
    }
}
