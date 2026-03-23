<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class DocumentTemplatePageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('document-templates/IndexPage');
    }

    public function show(string $uuid): Response
    {
        return Inertia::render('document-templates/ShowPage', [
            'uuid' => $uuid,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('document-templates/CreatePage');
    }

    public function edit(string $uuid): Response
    {
        return Inertia::render('document-templates/EditPage', [
            'uuid' => $uuid,
        ]);
    }
}
