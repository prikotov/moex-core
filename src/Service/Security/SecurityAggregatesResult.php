<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security;

use Moex\Core\Service\Security\Dto\SecurityAggregateDto;

final readonly class SecurityAggregatesResult
{
    /**
     * @param array<SecurityAggregateDto> $aggregates
     */
    public function __construct(
        public bool $success,
        public ?string $error = null,
        public array $aggregates = [],
    ) {
    }
}
