<?php

declare(strict_types=1);

namespace Src\Modules\Products\Infrastructure\Persistence\Repositories;

use Src\Modules\Products\Domain\Entities\Product;
use Src\Modules\Products\Domain\Ports\ProductRepositoryPort;
use Src\Modules\Products\Domain\ValueObjects\ProductId;
use Src\Modules\Products\Infrastructure\Persistence\Eloquent\Models\ProductEloquentModel;
use Src\Modules\Products\Infrastructure\Persistence\Mappers\ProductMapper;

class EloquentProductRepository implements ProductRepositoryPort
{
    public function __construct(
        private readonly ProductMapper $mapper
    ) {}

    public function find(ProductId $id): ?Product
    {
        $model = ProductEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(Product $product): void
    {
        $model = $this->mapper->toEloquent($product);
        $model->save();
    }

    public function softDelete(ProductId $id): void
    {
        ProductEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(ProductId $id): void
    {
        ProductEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }
}
