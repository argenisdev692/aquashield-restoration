<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Mappers;

use Src\Modules\Claims\Domain\Entities\Claim;
use Src\Modules\Claims\Domain\ValueObjects\ClaimId;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;

final class ClaimMapper
{
    public function toDomain(ClaimEloquentModel $model): Claim
    {
        return Claim::reconstitute(
            id: ClaimId::fromString($model->uuid),
            propertyId: (int) $model->property_id,
            signaturePathId: (int) $model->signature_path_id,
            typeDamageId: (int) $model->type_damage_id,
            userIdRefBy: (int) $model->user_id_ref_by,
            claimStatusId: (int) $model->claim_status,
            claimInternalId: $model->claim_internal_id,
            policyNumber: $model->policy_number,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
            claimNumber: $model->claim_number,
            dateOfLoss: $model->date_of_loss,
            descriptionOfLoss: $model->description_of_loss,
            numberOfFloors: $model->number_of_floors !== null ? (int) $model->number_of_floors : null,
            claimDate: $model->claim_date,
            workDate: $model->work_date,
            damageDescription: $model->damage_description,
            scopeOfWork: $model->scope_of_work,
            customerReviewed: $model->customer_reviewed,
        );
    }

    public function toEloquent(Claim $claim): ClaimEloquentModel
    {
        $model = ClaimEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $claim->id()->toString(),
        ]);

        $model->uuid              = $claim->id()->toString();
        $model->property_id       = $claim->propertyId();
        $model->signature_path_id = $claim->signaturePathId();
        $model->type_damage_id    = $claim->typeDamageId();
        $model->user_id_ref_by    = $claim->userIdRefBy();
        $model->claim_status      = $claim->claimStatusId();
        $model->claim_number      = $claim->claimNumber();
        $model->claim_internal_id = $claim->claimInternalId();
        $model->policy_number     = $claim->policyNumber();
        $model->date_of_loss      = $claim->dateOfLoss();
        $model->description_of_loss = $claim->descriptionOfLoss();
        $model->number_of_floors  = $claim->numberOfFloors();
        $model->claim_date        = $claim->claimDate();
        $model->work_date         = $claim->workDate();
        $model->damage_description = $claim->damageDescription();
        $model->scope_of_work     = $claim->scopeOfWork();
        $model->customer_reviewed = $claim->customerReviewed();

        return $model;
    }
}
