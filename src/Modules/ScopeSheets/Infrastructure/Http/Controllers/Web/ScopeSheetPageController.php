<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class ScopeSheetPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ScopeSheetsIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('ScopeSheetsCreatePage');
    }

    public function show(string $uuid): Response
    {
        return Inertia::render('ScopeSheetsShowPage', ['uuid' => $uuid]);
    }

    public function edit(string $uuid): Response
    {
        return Inertia::render('ScopeSheetsEditPage', ['uuid' => $uuid]);
    }
}
