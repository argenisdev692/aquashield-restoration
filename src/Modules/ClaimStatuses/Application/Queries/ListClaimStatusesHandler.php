<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\ClaimStatuses\Application\DTOs\ClaimStatusFilterData;
use Src\Modules\ClaimStatuses\Application\Queries\ReadModels\ClaimStatusListReadModel;
use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Eloquent\Models\ClaimStatusEloquentModel;

final class ListClaimStatusesHandler
{
    public function handle(ClaimStatusFilterData $filters): LengthAwarePaginator
    {
        $query = ClaimStatusEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'claim_status_name',
                'background_color',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where('claim_status_name', 'like', "%{$search}%");
            })
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('claim_status_name')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (ClaimStatusEloquentModel $model): ClaimStatusListReadModel => new ClaimStatusListReadModel(
                uuid: $model->uuid,
                claimStatusName: $model->claim_status_name,
                backgroundColor: $model->background_color,
                createdAt: $model->created_at?->toIso8601String() ?? '',
                deletedAt: $model->deleted_at?->toIso8601String(),
            ));
    }
}
