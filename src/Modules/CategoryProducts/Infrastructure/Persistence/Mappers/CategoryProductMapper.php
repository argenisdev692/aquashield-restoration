<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Infrastructure\Persistence\Mappers;

use Src\Modules\CategoryProducts\Domain\Entities\CategoryProduct;
use Src\Modules\CategoryProducts\Domain\ValueObjects\CategoryProductId;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models\CategoryProductEloquentModel;

final class CategoryProductMapper
{
    public function toDomain(CategoryProductEloquentModel $model): CategoryProduct
    {
        return CategoryProduct::reconstitute(
            id: CategoryProductId::fromString($model->uuid),
            categoryProductName: $model->category_product_name,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(CategoryProduct $categoryProduct): CategoryProductEloquentModel
    {
        $model = CategoryProductEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $categoryProduct->id()->toString(),
        ]);

        $model->uuid = $categoryProduct->id()->toString();
        $model->category_product_name = $categoryProduct->categoryProductName();

        return $model;
    }
}
