<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\ServiceCategories\Application\DTOs\ServiceCategoryFilterData;
use Src\Modules\ServiceCategories\Application\Queries\ReadModels\ServiceCategoryListReadModel;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class ListServiceCategoriesHandler
{
    public function handle(ServiceCategoryFilterData $filters): LengthAwarePaginator
    {
        $query = ServiceCategoryEloquentModel::query()
            ->withTrashed()
            ->select(['uuid', 'category', 'type', 'created_at', 'deleted_at'])
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('category', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('category')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (ServiceCategoryEloquentModel $m): ServiceCategoryListReadModel => new ServiceCategoryListReadModel(
                uuid: $m->uuid,
                category: $m->category,
                type: $m->type,
                createdAt: $m->created_at?->toIso8601String() ?? '',
                deletedAt: $m->deleted_at?->toIso8601String(),
            ));
    }
}
