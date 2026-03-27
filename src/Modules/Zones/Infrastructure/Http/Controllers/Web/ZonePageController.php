<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class ZonePageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('zones/ZonesIndexPage');
    }

    public function show(string $uuid): Response
    {
        return Inertia::render('zones/ZoneShowPage', ['uuid' => $uuid]);
    }

    public function create(): Response
    {
        return Inertia::render('zones/ZoneCreatePage');
    }

    public function edit(string $uuid): Response
    {
        return Inertia::render('zones/ZoneEditPage', ['uuid' => $uuid]);
    }
}
