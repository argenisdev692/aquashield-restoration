<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\ServiceRequests\Application\DTOs\ServiceRequestFilterData;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models\ServiceRequestEloquentModel;

final class ServiceRequestExportQuery
{
    public static function build(ServiceRequestFilterData $filters): Builder
    {
        return ServiceRequestEloquentModel::query()
            ->withTrashed()
            ->select(['uuid', 'requested_service', 'created_at', 'deleted_at'])
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('requested_service', 'like', "%{$search}%");
                });
            })
            ->when($filters->status === 'active', static fn (Builder $builder): Builder => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom): Builder => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo): Builder => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('requested_service')
            ->orderByDesc('created_at');
    }
}
