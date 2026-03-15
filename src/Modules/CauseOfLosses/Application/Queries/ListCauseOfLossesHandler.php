<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\CauseOfLosses\Application\DTOs\CauseOfLossFilterData;
use Src\Modules\CauseOfLosses\Application\Queries\ReadModels\CauseOfLossListReadModel;
use Src\Modules\CauseOfLosses\Infrastructure\Persistence\Eloquent\Models\CauseOfLossEloquentModel;

final class ListCauseOfLossesHandler
{
    public function handle(CauseOfLossFilterData $filters): LengthAwarePaginator
    {
        $query = CauseOfLossEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'cause_loss_name',
                'description',
                'severity',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('cause_loss_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters->severity, static fn ($builder, string $severity) => $builder->where('severity', $severity))
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('cause_loss_name')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (CauseOfLossEloquentModel $causeOfLoss): CauseOfLossListReadModel => new CauseOfLossListReadModel(
                uuid: $causeOfLoss->uuid,
                causeLossName: $causeOfLoss->cause_loss_name,
                description: $causeOfLoss->description,
                severity: $causeOfLoss->severity,
                createdAt: $causeOfLoss->created_at?->toIso8601String() ?? '',
                deletedAt: $causeOfLoss->deleted_at?->toIso8601String(),
            ));
    }
}
