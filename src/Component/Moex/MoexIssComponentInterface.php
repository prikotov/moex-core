<?php

declare(strict_types=1);

namespace Moex\Skill\Component\Moex;

use Moex\Skill\Exception\InfrastructureExceptionInterface;

interface MoexIssComponentInterface
{
    /**
     * @throws InfrastructureExceptionInterface
     */
    public function getContent(string $url, array $urlData, array $query): ?string;
}
