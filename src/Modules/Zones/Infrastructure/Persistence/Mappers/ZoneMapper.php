<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Persistence\Mappers;

use Src\Modules\Zones\Domain\Entities\Zone;
use Src\Modules\Zones\Domain\ValueObjects\ZoneId;
use Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models\ZoneEloquentModel;

final class ZoneMapper
{
    public function toDomain(ZoneEloquentModel $model): Zone
    {
        return Zone::reconstitute(
            id: ZoneId::fromString($model->uuid),
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

    public function toEloquent(Zone $zone): ZoneEloquentModel
    {
        $model = ZoneEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $zone->id()->toString(),
        ]);

        $model->uuid        = $zone->id()->toString();
        $model->zone_name   = $zone->zoneName();
        $model->zone_type   = $zone->zoneType();
        $model->code        = $zone->code();
        $model->description = $zone->description();
        $model->user_id     = $zone->userId();

        return $model;
    }
}
