<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security;

use Moex\Core\Service\Security\Dto\SecuritySearchDto;

final readonly class SecuritySearchResult
{
    /**
     * @param array<SecuritySearchDto> $securities
     */
    public function __construct(
        public bool $success,
        public ?string $error = null,
        public array $securities = [],
    ) {
    }
}
