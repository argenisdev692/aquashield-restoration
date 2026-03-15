<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Infrastructure\Persistence\Repositories;

use Src\Modules\CategoryProducts\Domain\Entities\CategoryProduct;
use Src\Modules\CategoryProducts\Domain\Ports\CategoryProductRepositoryPort;
use Src\Modules\CategoryProducts\Domain\ValueObjects\CategoryProductId;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models\CategoryProductEloquentModel;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Mappers\CategoryProductMapper;

final class EloquentCategoryProductRepository implements CategoryProductRepositoryPort
{
    public function __construct(
        private readonly CategoryProductMapper $mapper,
    ) {}

    public function find(CategoryProductId $id): ?CategoryProduct
    {
        $model = CategoryProductEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(CategoryProduct $categoryProduct): void
    {
        $this->mapper->toEloquent($categoryProduct)->save();
    }

    public function softDelete(CategoryProductId $id): void
    {
        CategoryProductEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(CategoryProductId $id): void
    {
        CategoryProductEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (CategoryProductId $id): string => $id->toString(),
            $ids,
        );

        return CategoryProductEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
