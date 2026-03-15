<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Persistence\Repositories;

use Src\Modules\TypeDamages\Domain\Entities\TypeDamage;
use Src\Modules\TypeDamages\Domain\Ports\TypeDamageRepositoryPort;
use Src\Modules\TypeDamages\Domain\ValueObjects\TypeDamageId;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Mappers\TypeDamageMapper;

final class EloquentTypeDamageRepository implements TypeDamageRepositoryPort
{
    public function __construct(
        private readonly TypeDamageMapper $mapper,
    ) {}

    public function find(TypeDamageId $id): ?TypeDamage
    {
        $model = TypeDamageEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(TypeDamage $typeDamage): void
    {
        $this->mapper->toEloquent($typeDamage)->save();
    }

    public function softDelete(TypeDamageId $id): void
    {
        TypeDamageEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(TypeDamageId $id): void
    {
        TypeDamageEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (TypeDamageId $id): string => $id->toString(),
            $ids,
        );

        return TypeDamageEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
