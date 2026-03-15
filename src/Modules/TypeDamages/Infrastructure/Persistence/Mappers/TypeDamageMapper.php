<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Persistence\Mappers;

use Src\Modules\TypeDamages\Domain\Entities\TypeDamage;
use Src\Modules\TypeDamages\Domain\ValueObjects\TypeDamageId;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;

final class TypeDamageMapper
{
    public function toDomain(TypeDamageEloquentModel $model): TypeDamage
    {
        return TypeDamage::reconstitute(
            id: TypeDamageId::fromString($model->uuid),
            typeDamageName: $model->type_damage_name,
            description: $model->description,
            severity: $model->severity,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(TypeDamage $typeDamage): TypeDamageEloquentModel
    {
        $model = TypeDamageEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $typeDamage->id()->toString(),
        ]);

        $model->uuid = $typeDamage->id()->toString();
        $model->type_damage_name = $typeDamage->typeDamageName();
        $model->description = $typeDamage->description();
        $model->severity = $typeDamage->severity();

        return $model;
    }
}
