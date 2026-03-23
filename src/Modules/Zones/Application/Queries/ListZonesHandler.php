<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Application\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Zones\Application\DTOs\ZoneFilterData;
use Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models\ZoneEloquentModel;

final class ListZonesHandler
{
    public function handle(ZoneFilterData $filters): LengthAwarePaginator
    {
        return ZoneEloquentModel::query()
            ->withTrashed()
            ->select(['uuid', 'zone_name', 'zone_type', 'code', 'description', 'user_id', 'created_at', 'deleted_at'])
            ->when(
                $filters->search,
                static fn (Builder $q, string $s): Builder => $q->where(
                    static fn (Builder $b): Builder => $b
                        ->where('zone_name', 'like', "%{$s}%")
                        ->orWhere('code', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%"),
                ),
            )
            ->when($filters->zoneType, static fn (Builder $q, string $v): Builder => $q->where('zone_type', $v))
            ->when($filters->status === 'active',  static fn (Builder $q): Builder => $q->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $q): Builder => $q->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $q, string $d): Builder => $q->whereDate('created_at', '>=', $d))
            ->when($filters->dateTo,   static fn (Builder $q, string $d): Builder => $q->whereDate('created_at', '<=', $d))
            ->orderBy('zone_name')
            ->orderByDesc('created_at')
            ->paginate($filters->perPage, ['*'], 'page', $filters->page);
    }
}
