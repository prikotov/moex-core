<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security;

use Moex\Core\Service\Security\Dto\SecurityIndexDto;

final readonly class SecurityIndicesResult
{
    /**
     * @param array<SecurityIndexDto> $indices
     */
    public function __construct(
        public bool $success,
        public ?string $error = null,
        public array $indices = [],
    ) {
    }
}
