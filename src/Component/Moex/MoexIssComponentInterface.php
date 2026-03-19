<?php

declare(strict_types=1);

namespace Moex\Core\Component\Moex;

use Moex\Core\Exception\InfrastructureExceptionInterface;

interface MoexIssComponentInterface
{
    /**
     * @param array<string> $urlData
     * @param array<string, string|int> $query
     * @throws InfrastructureExceptionInterface
     */
    public function getContent(string $url, array $urlData, array $query): ?string;
}
