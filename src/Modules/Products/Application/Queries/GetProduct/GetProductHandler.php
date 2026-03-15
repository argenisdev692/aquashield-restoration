<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Queries\GetProduct;

use Illuminate\Support\Facades\Cache;
use Src\Modules\Products\Application\Queries\ReadModels\ProductReadModel;
use Src\Modules\Products\Infrastructure\Persistence\Eloquent\Models\ProductEloquentModel;

class GetProductHandler
{
    public function handle(GetProductQuery $query): ?ProductReadModel
    {
        $cacheKey = "product_{$query->uuid}";

        return Cache::remember($cacheKey, 60 * 5, function () use ($query) {
            $product = ProductEloquentModel::with(['categoryProduct'])
                ->select([
                    'id',
                    'uuid',
                    'product_category_id',
                    'product_name',
                    'product_description',
                    'price',
                    'unit',
                    'order_position',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ])
                ->where('uuid', $query->uuid)
                ->first();

            if (!$product) {
                return null;
            }

            return new ProductReadModel(
                uuid: $product->uuid,
                categoryId: $product->categoryProduct?->uuid ?? '',
                categoryName: $product->categoryProduct?->category_product_name ?? '',
                name: $product->product_name,
                description: $product->product_description,
                price: (float) $product->price,
                unit: $product->unit,
                orderPosition: $product->order_position,
                createdAt: $product->created_at?->toIso8601String() ?? '',
                updatedAt: $product->updated_at?->toIso8601String() ?? '',
                deletedAt: $product->deleted_at?->toIso8601String()
            );
        });
    }
}
