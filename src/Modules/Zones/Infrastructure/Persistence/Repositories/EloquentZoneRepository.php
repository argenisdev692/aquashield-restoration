<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Persistence\Repositories;

use Src\Modules\Zones\Domain\Entities\Zone;
use Src\Modules\Zones\Domain\Ports\ZoneRepositoryPort;
use Src\Modules\Zones\Domain\ValueObjects\ZoneId;
use Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models\ZoneEloquentModel;
use Src\Modules\Zones\Infrastructure\Persistence\Mappers\ZoneMapper;

final class EloquentZoneRepository implements ZoneRepositoryPort
{
    public function __construct(
        private readonly ZoneMapper $mapper,
    ) {}

    public function find(ZoneId $id): ?Zone
    {
        $model = ZoneEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(Zone $zone): void
    {
        $this->mapper->toEloquent($zone)->save();
    }

    public function softDelete(ZoneId $id): void
    {
        ZoneEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(ZoneId $id): void
    {
        ZoneEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (ZoneId $id): string => $id->toString(),
            $ids,
        );

        return ZoneEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
