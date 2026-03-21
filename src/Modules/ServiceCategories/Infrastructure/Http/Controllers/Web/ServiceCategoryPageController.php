<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\ServiceCategories\Application\Queries\GetServiceCategoryHandler;

final class ServiceCategoryPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('service-categories/ServiceCategoriesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('service-categories/ServiceCategoryCreatePage');
    }

    public function show(string $uuid, GetServiceCategoryHandler $handler): Response
    {
        $serviceCategory = $handler->handle($uuid);

        if ($serviceCategory === null) {
            abort(404);
        }

        return Inertia::render('service-categories/ServiceCategoryShowPage', [
            'serviceCategory' => $serviceCategory,
        ]);
    }

    public function edit(string $uuid, GetServiceCategoryHandler $handler): Response
    {
        $serviceCategory = $handler->handle($uuid);

        if ($serviceCategory === null) {
            abort(404);
        }

        return Inertia::render('service-categories/ServiceCategoryEditPage', [
            'serviceCategory' => $serviceCategory,
        ]);
    }
}
