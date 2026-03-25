<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Persistence\Repositories;

use Src\Modules\Properties\Domain\Entities\Property;
use Src\Modules\Properties\Domain\Ports\PropertyRepositoryPort;
use Src\Modules\Properties\Domain\ValueObjects\PropertyId;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\PropertyEloquentModel;
use Src\Modules\Properties\Infrastructure\Persistence\Mappers\PropertyMapper;

final class EloquentPropertyRepository implements PropertyRepositoryPort
{
    public function __construct(
        private readonly PropertyMapper $mapper,
    ) {}

    public function find(PropertyId $id): ?Property
    {
        $model = PropertyEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(Property $property): void
    {
        $this->mapper->toEloquent($property)->save();
    }

    public function softDelete(PropertyId $id): void
    {
        PropertyEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(PropertyId $id): void
    {
        PropertyEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (PropertyId $id): string => $id->toString(),
            $ids,
        );

        return PropertyEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
