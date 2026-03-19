<?php

declare(strict_types=1);

namespace Moex\Core\Service\Security;

use Generator;
use Moex\Core\Service\Security\Dto\SecurityAggregateDto;
use Moex\Core\Service\Security\Dto\SecurityIndexDto;
use Moex\Core\Service\Security\Dto\SecuritySpecificationDto;
use Moex\Core\Service\Security\Dto\SecurityTradeDataDto;

interface SecurityServiceInterface
{
    public function getSpecification(string $security): SecuritySpecificationsResult;

    public function getTradeData(string $security): SecurityTradeDataResult;

    public function getAggregates(string $security, ?string $date = null): SecurityAggregatesResult;

    public function getIndices(string $security): SecurityIndicesResult;
}
