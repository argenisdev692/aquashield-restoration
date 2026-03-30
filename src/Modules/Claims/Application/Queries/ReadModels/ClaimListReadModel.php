<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\Queries\ReadModels;

final class ClaimListReadModel
{
    public function __construct(
        public string $uuid,
        public ?string $claimNumber,
        public string $claimInternalId,
        public string $policyNumber,
        public ?string $dateOfLoss,
        public int $propertyId,
        public ?string $propertyAddress,
        public array $customers,
        public int $typeDamageId,
        public ?string $typeDamageName,
        public int $claimStatusId,
        public ?string $claimStatusName,
        public ?string $claimStatusColor,
        public int $userIdRefBy,
        public ?string $referredByName,
        public string $status,
        public string $createdAt,
        public ?string $deletedAt,
    ) {}
}
