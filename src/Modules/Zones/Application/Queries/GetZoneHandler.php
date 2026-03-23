<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Application\Queries;

use Src\Modules\Zones\Application\Queries\ReadModels\ZoneReadModel;
use Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models\ZoneEloquentModel;

final class GetZoneHandler
{
    public function handle(string $uuid): ?ZoneReadModel
    {
        $model = ZoneEloquentModel::withTrashed()
            ->whereUuid($uuid)
            ->first();

        if ($model === null) {
            return null;
        }

        return new ZoneReadModel(
            uuid: $model->uuid,
            zoneName: $model->zone_name,
            zoneType: $model->zone_type,
            code: $model->code,
            description: $model->description,
            userId: (int) $model->user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }
}
