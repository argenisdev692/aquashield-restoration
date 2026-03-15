<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\CategoryProducts\Application\DTOs\CategoryProductFilterData;
use Src\Modules\CategoryProducts\Application\Queries\ReadModels\CategoryProductListReadModel;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models\CategoryProductEloquentModel;

final class ListCategoryProductsHandler
{
    public function handle(CategoryProductFilterData $filters): LengthAwarePaginator
    {
        $query = CategoryProductEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'category_product_name',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static fn ($builder, string $search) => $builder->where('category_product_name', 'like', "%{$search}%"))
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('category_product_name')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (CategoryProductEloquentModel $categoryProduct): CategoryProductListReadModel => new CategoryProductListReadModel(
                uuid: $categoryProduct->uuid,
                categoryProductName: $categoryProduct->category_product_name,
                createdAt: $categoryProduct->created_at?->toIso8601String() ?? '',
                deletedAt: $categoryProduct->deleted_at?->toIso8601String(),
            ));
    }
}
