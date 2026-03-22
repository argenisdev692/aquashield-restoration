<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Infrastructure\Persistence\Mappers;

use Src\Modules\ClaimStatuses\Domain\Entities\ClaimStatus;
use Src\Modules\ClaimStatuses\Domain\ValueObjects\ClaimStatusId;
use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Eloquent\Models\ClaimStatusEloquentModel;

final class ClaimStatusMapper
{
    public function toDomain(ClaimStatusEloquentModel $model): ClaimStatus
    {
        return ClaimStatus::reconstitute(
            id: ClaimStatusId::fromString($model->uuid),
            claimStatusName: $model->claim_status_name,
            backgroundColor: $model->background_color,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(ClaimStatus $claimStatus): ClaimStatusEloquentModel
    {
        $model = ClaimStatusEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $claimStatus->id()->toString(),
        ]);

        $model->uuid = $claimStatus->id()->toString();
        $model->claim_status_name = $claimStatus->claimStatusName();
        $model->background_color = $claimStatus->backgroundColor();

        return $model;
    }
}
