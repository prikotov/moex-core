<?php

declare(strict_types=1);

namespace Moex\Skill\Service\Security;

use Generator;
use Moex\Skill\Service\Security\Dto\SecurityAggregateDto;
use Moex\Skill\Service\Security\Dto\SecurityIndexDto;
use Moex\Skill\Service\Security\Dto\SecuritySpecificationDto;
use Moex\Skill\Service\Security\Dto\SecurityTradeDataDto;

interface SecurityServiceInterface
{
    public function getSpecification(string $security): SecuritySpecificationsResult;

    public function getTradeData(string $security): SecurityTradeDataResult;

    public function getAggregates(string $security, ?string $date = null): SecurityAggregatesResult;

    public function getIndices(string $security): SecurityIndicesResult;
}
