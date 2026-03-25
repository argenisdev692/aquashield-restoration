<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\Properties\Application\DTOs\PropertyFilterData;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\PropertyEloquentModel;

final class PropertyExportQuery
{
    public static function build(PropertyFilterData $filters): Builder
    {
        return PropertyEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'property_address',
                'property_address_2',
                'property_state',
                'property_city',
                'property_postal_code',
                'property_country',
                'property_latitude',
                'property_longitude',
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
    }
}
