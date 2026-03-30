<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Application\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Properties\Application\DTOs\PropertyFilterData;
use Src\Modules\Properties\Application\Queries\ReadModels\PropertyListReadModel;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\PropertyEloquentModel;

final class ListPropertiesHandler
{
    public function handle(PropertyFilterData $filters): LengthAwarePaginator
    {
        $query = PropertyEloquentModel::query()
            ->withTrashed()
            ->select([
                'id',
                'uuid',
                'property_address',
                'property_address_2',
                'property_state',
                'property_city',
                'property_postal_code',
                'property_country',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('property_address', 'like', "%{$search}%")
                        ->orWhere('property_city', 'like', "%{$search}%")
                        ->orWhere('property_state', 'like', "%{$search}%")
                        ->orWhere('property_postal_code', 'like', "%{$search}%")
                        ->orWhere('property_country', 'like', "%{$search}%");
                });
            })
            ->when($filters->status === 'active', static fn (Builder $b): Builder => $b->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $b): Builder => $b->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $b, string $d): Builder => $b->whereDate('created_at', '>=', $d))
            ->when($filters->dateTo, static fn (Builder $b, string $d): Builder => $b->whereDate('created_at', '<=', $d))
            ->orderBy('property_address')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (PropertyEloquentModel $m): PropertyListReadModel => new PropertyListReadModel(
                propertyId: (int) $m->id,
                uuid: $m->uuid,
                propertyAddress: $m->property_address,
                propertyAddress2: $m->property_address_2,
                propertyState: $m->property_state,
                propertyCity: $m->property_city,
                propertyPostalCode: $m->property_postal_code,
                propertyCountry: $m->property_country,
                createdAt: $m->created_at?->toIso8601String() ?? '',
                deletedAt: $m->deleted_at?->toIso8601String(),
            ));
    }
}
