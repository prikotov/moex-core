<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security;

use Moex\Core\Service\Security\Dto\SecurityTradeDataDto;

final readonly class SecurityTradeDataResult
{
    /**
     * @param array<SecurityTradeDataDto> $securities
     * @param array<SecurityTradeDataDto> $marketData
     */
    public function __construct(
        public bool $success,
        public ?string $error = null,
        public array $securities = [],
        public array $marketData = [],
    ) {
    }
}
