<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\CategoryProducts\Application\Queries\GetCategoryProductHandler;

final class CategoryProductPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('category-products/CategoryProductsIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('category-products/CategoryProductCreatePage');
    }

    public function show(string $uuid, GetCategoryProductHandler $handler): Response
    {
        $categoryProduct = $handler->handle($uuid);

        if ($categoryProduct === null) {
            abort(404);
        }

        return Inertia::render('category-products/CategoryProductShowPage', [
            'categoryProduct' => $categoryProduct,
        ]);
    }

    public function edit(string $uuid, GetCategoryProductHandler $handler): Response
    {
        $categoryProduct = $handler->handle($uuid);

        if ($categoryProduct === null) {
            abort(404);
        }

        return Inertia::render('category-products/CategoryProductEditPage', [
            'categoryProduct' => $categoryProduct,
        ]);
    }
}
