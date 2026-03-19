<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security;

use Moex\Core\Service\Security\Dto\SecuritySpecificationDto;

final readonly class SecuritySpecificationsResult
{
    /**
     * @param array<SecuritySpecificationDto> $specifications
     */
    public function __construct(
        public bool $success,
        public ?string $error = null,
        public array $specifications = [],
    ) {
    }
}
