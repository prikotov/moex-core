<?php

declare(strict_types=1);

namespace Moex\Skill\Service\Security\Dto;

final readonly class SecurityIndexDto
{
    public function __construct(
        public string $secid,
        public string $shortName,
        public string $from,
        public ?string $till,
        public ?float $currentValue,
        public ?float $lastChangePrc,
        public ?float $lastChange,
    ) {
    }
}
