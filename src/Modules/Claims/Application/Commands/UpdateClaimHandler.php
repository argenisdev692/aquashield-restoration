<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\Commands;

use RuntimeException;
use Src\Modules\Claims\Application\DTOs\UpdateClaimData;
use Src\Modules\Claims\Domain\Ports\ClaimRepositoryPort;

final class UpdateClaimHandler
{
    public function __construct(
        private readonly ClaimRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateClaimData $data): void
    {
        $claim = $this->repository->findByUuid($uuid);

        if ($claim === null) {
            throw new RuntimeException("Claim [{$uuid}] not found.");
        }

        $claim->update(
            propertyId: $data->propertyId,
            signaturePathId: $data->signaturePathId,
            typeDamageId: $data->typeDamageId,
            userIdRefBy: $data->userIdRefBy,
            claimStatusId: $data->claimStatus,
            policyNumber: $data->policyNumber,
            updatedAt: now()->toIso8601String(),
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

        $this->repository->syncRelations(
            $uuid,
            $data->causeOfLossIds ?? [],
            $data->serviceRequestIds ?? [],
        );
    }
}
