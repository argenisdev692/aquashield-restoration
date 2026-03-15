<?php

declare(strict_types=1);

namespace Src\Modules\Products\Infrastructure\Persistence\Mappers;

use Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models\CategoryProductEloquentModel;
use Src\Modules\Products\Domain\Entities\Product;
use Src\Modules\Products\Domain\ValueObjects\ProductId;
use Src\Modules\Products\Infrastructure\Persistence\Eloquent\Models\ProductEloquentModel;

class ProductMapper
{
    public function toDomain(ProductEloquentModel $model): Product
    {
        return Product::create(
            id: ProductId::fromString($model->uuid),
            categoryId: CategoryProductEloquentModel::withTrashed()
                ->whereKey($model->product_category_id)
                ->value('uuid') ?? '',
            name: $model->product_name,
            description: $model->product_description,
            price: (float) $model->price,
            unit: $model->unit,
            orderPosition: $model->order_position,
            createdAt: $model->created_at?->toIso8601String() ?? ''
        );
    }

    public function toEloquent(Product $product): ProductEloquentModel
    {
        $categoryId = CategoryProductEloquentModel::withTrashed()
            ->where('uuid', $product->categoryId())
            ->value('id');

        $model = ProductEloquentModel::firstOrNew(['uuid' => $product->id()->toString()]);
        $model->uuid = $product->id()->toString();
        $model->product_category_id = $categoryId;
        $model->product_name = $product->name();
        $model->product_description = $product->description();
        $model->price = $product->price();
        $model->unit = $product->unit();
        $model->order_position = $product->orderPosition();

        return $model;
    }
}
