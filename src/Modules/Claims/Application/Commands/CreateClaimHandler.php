<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\Commands;

use Src\Modules\Claims\Application\DTOs\StoreClaimData;
use Src\Modules\Claims\Domain\Entities\Claim;
use Src\Modules\Claims\Domain\Ports\ClaimInternalIdGeneratorPort;
use Src\Modules\Claims\Domain\Ports\ClaimRepositoryPort;
use Src\Modules\Claims\Domain\ValueObjects\ClaimId;

final class CreateClaimHandler
{
    public function __construct(
        private readonly ClaimRepositoryPort $repository,
        private readonly ClaimInternalIdGeneratorPort $idGenerator,
    ) {}

    #[\NoDiscard('UUID of the created claim must be captured')]
    public function handle(StoreClaimData $data): string
    {
        $claim = Claim::create(
            id: ClaimId::generate(),
            propertyId: $data->propertyId,
            signaturePathId: $data->signaturePathId,
            typeDamageId: $data->typeDamageId,
            userIdRefBy: $data->userIdRefBy,
            claimStatusId: $data->claimStatus,
            claimInternalId: $this->idGenerator->nextId(),
            policyNumber: $data->policyNumber,
            createdAt: now()->toIso8601String(),
            claimNumber: $data->claimNumber,
            dateOfLoss: $data->dateOfLoss,
            descriptionOfLoss: $data->descriptionOfLoss,
            numberOfFloors: $data->numberOfFloors,
            claimDate: $data->claimDate,
            workDate: $data->workDate,
            damageDescription: $data->damageDescription,
            scopeOfWork: $data->scopeOfWork,
            customerReviewed: $data->customerReviewed,
        );

        $this->repository->save($claim);

        $uuid = $claim->id()->toString();

        $this->repository->syncRelations(
            $uuid,
            $data->causeOfLossIds ?? [],
            $data->serviceRequestIds ?? [],
        );

        return $uuid;
    }
}
