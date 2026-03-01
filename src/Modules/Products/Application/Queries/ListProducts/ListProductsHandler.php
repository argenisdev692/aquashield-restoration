<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Queries\ListProducts;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Src\Modules\Products\Application\Queries\ReadModels\ProductListReadModel;

class ListProductsHandler
{
    public function handle(ListProductsQuery $query): LengthAwarePaginator
    {
        $filters = $query->filters;
        $cacheKey = "products_list_" . md5(json_encode($filters));

        try {
            return Cache::tags(['products_list'])->remember($cacheKey, 60 * 2, fn() => $this->fetchData($filters));
        } catch (\Exception $e) {
            return Cache::remember($cacheKey, 60 * 2, fn() => $this->fetchData($filters));
        }
    }

    private function fetchData($filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->select([
                'products.uuid',
                'products.product_name',
                'products.price',
                'products.unit',
                'products.order_position',
                'products.created_at',
                'products.deleted_at',
                'category_products.category_product_name'
            ])
            ->join('category_products', 'products.product_category_id', '=', 'category_products.id')
            ->when($filters->search, fn($q, $search) =>
                $q->where('products.product_name', 'like', "%{$search}%")
            )
            ->when($filters->categoryId, fn($q, $categoryId) =>
                $q->where('category_products.uuid', $categoryId)
            )
            ->when($filters->status === 'deleted', fn($q) =>
                $q->onlyTrashed()
            )
            ->when($filters->status === 'active', fn($q) =>
                $q->whereNull('products.deleted_at')
            )
            ->when($filters->dateFrom && $filters->dateTo, fn($q) =>
                $q->whereBetween('products.created_at', [$filters->dateFrom, $filters->dateTo])
            )
            ->orderBy('products.order_position', 'asc')
            ->orderBy('products.created_at', 'desc');

        return $query->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(fn($product) => new ProductListReadModel(
                uuid: $product->uuid,
                categoryName: $product->category_product_name,
                name: $product->product_name,
                price: (float) $product->price,
                unit: $product->unit,
                orderPosition: (int) ($product->order_position ?? 0),
                createdAt: $product->created_at?->toIso8601String() ?? '',
                deletedAt: $product->deleted_at?->toIso8601String()
            ));
    }
}
