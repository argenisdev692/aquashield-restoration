<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Infrastructure\Persistence\Mappers;

use Src\Modules\CauseOfLosses\Domain\Entities\CauseOfLoss;
use Src\Modules\CauseOfLosses\Domain\ValueObjects\CauseOfLossId;
use Src\Modules\CauseOfLosses\Infrastructure\Persistence\Eloquent\Models\CauseOfLossEloquentModel;

final class CauseOfLossMapper
{
    public function toDomain(CauseOfLossEloquentModel $model): CauseOfLoss
    {
        return CauseOfLoss::reconstitute(
            id: CauseOfLossId::fromString($model->uuid),
            causeLossName: $model->cause_loss_name,
            description: $model->description,
            severity: $model->severity,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(CauseOfLoss $causeOfLoss): CauseOfLossEloquentModel
    {
        $model = CauseOfLossEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $causeOfLoss->id()->toString(),
        ]);

        $model->uuid = $causeOfLoss->id()->toString();
        $model->cause_loss_name = $causeOfLoss->causeLossName();
        $model->description = $causeOfLoss->description();
        $model->severity = $causeOfLoss->severity();

        return $model;
    }
}
