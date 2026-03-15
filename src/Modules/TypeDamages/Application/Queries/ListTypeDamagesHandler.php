<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\TypeDamages\Application\DTOs\TypeDamageFilterData;
use Src\Modules\TypeDamages\Application\Queries\ReadModels\TypeDamageListReadModel;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;

final class ListTypeDamagesHandler
{
    public function handle(TypeDamageFilterData $filters): LengthAwarePaginator
    {
        $query = TypeDamageEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'type_damage_name',
                'description',
                'severity',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function ($builder, string $search) {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('type_damage_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters->severity, static fn ($builder, string $severity) => $builder->where('severity', $severity))
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('type_damage_name')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (TypeDamageEloquentModel $typeDamage): TypeDamageListReadModel => new TypeDamageListReadModel(
                uuid: $typeDamage->uuid,
                typeDamageName: $typeDamage->type_damage_name,
                description: $typeDamage->description,
                severity: $typeDamage->severity,
                createdAt: $typeDamage->created_at?->toIso8601String() ?? '',
                deletedAt: $typeDamage->deleted_at?->toIso8601String(),
            ));
    }
}
