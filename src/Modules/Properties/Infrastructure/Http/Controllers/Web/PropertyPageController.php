<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\Properties\Application\Queries\GetPropertyHandler;

final class PropertyPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('properties/PropertiesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('properties/PropertyCreatePage');
    }

    public function show(string $uuid, GetPropertyHandler $handler): Response
    {
        $property = $handler->handle($uuid);

        if ($property === null) {
            abort(404);
        }

        return Inertia::render('properties/PropertyShowPage', [
            'property' => $property,
        ]);
    }

    public function edit(string $uuid, GetPropertyHandler $handler): Response
    {
        $property = $handler->handle($uuid);

        if ($property === null) {
            abort(404);
        }

        return Inertia::render('properties/PropertyEditPage', [
            'property' => $property,
        ]);
    }
}
