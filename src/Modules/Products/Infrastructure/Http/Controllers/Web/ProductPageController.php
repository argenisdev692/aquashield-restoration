<?php

declare(strict_types=1);

namespace Src\Modules\Products\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models\CategoryProductEloquentModel;
use Src\Modules\Products\Application\Queries\GetProduct\GetProductHandler;
use Src\Modules\Products\Application\Queries\GetProduct\GetProductQuery;

class ProductPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('products/ProductsIndexPage');
    }

    public function create(): Response
    {
        $categories = CategoryProductEloquentModel::query()
            ->select(['uuid', 'category_product_name'])
            ->orderBy('category_product_name')
            ->get();

        return Inertia::render('products/ProductCreatePage', [
            'categories' => $categories
        ]);
    }

    public function show(string $uuid, GetProductHandler $handler): Response
    {
        $product = $handler->handle(new GetProductQuery($uuid));

        if (!$product) {
            abort(404);
        }

        return Inertia::render('products/ProductShowPage', [
            'product' => $product
        ]);
    }

    public function edit(string $uuid, GetProductHandler $handler): Response
    {
        $product = $handler->handle(new GetProductQuery($uuid));

        if (!$product) {
            abort(404);
        }

        $categories = CategoryProductEloquentModel::query()
            ->select(['uuid', 'category_product_name'])
            ->orderBy('category_product_name')
            ->get();

        return Inertia::render('products/ProductEditPage', [
            'product' => $product,
            'categories' => $categories
        ]);
    }
}
