<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Application\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\ServiceRequests\Application\DTOs\ServiceRequestFilterData;
use Src\Modules\ServiceRequests\Application\Queries\ReadModels\ServiceRequestListReadModel;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models\ServiceRequestEloquentModel;

final class ListServiceRequestsHandler
{
    public function handle(ServiceRequestFilterData $filters): LengthAwarePaginator
    {
        $query = ServiceRequestEloquentModel::query()
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

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (ServiceRequestEloquentModel $model): ServiceRequestListReadModel => new ServiceRequestListReadModel(
                uuid: $model->uuid,
                requestedService: $model->requested_service,
                createdAt: $model->created_at?->toIso8601String() ?? '',
                deletedAt: $model->deleted_at?->toIso8601String(),
            ));
    }
}
