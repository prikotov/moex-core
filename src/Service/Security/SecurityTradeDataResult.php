<?php

declare(strict_types=1);

namespace Moex\Skill\Service\Security;

final readonly class SecurityTradeDataResult
{
    public function __construct(
        public bool $success,
        public ?string $error = null,
        public array $securities = [],
        public array $marketData = [],
    ) {
    }
}
