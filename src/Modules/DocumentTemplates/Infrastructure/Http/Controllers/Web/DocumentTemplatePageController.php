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
        return Inertia::render('document-templates/DocumentTemplatesIndexPage');
    }

    public function show(string $uuid): Response
    {
        return Inertia::render('document-templates/DocumentTemplateShowPage', [
            'uuid' => $uuid,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('document-templates/DocumentTemplateCreatePage');
    }

    public function edit(string $uuid): Response
    {
        return Inertia::render('document-templates/DocumentTemplateEditPage', [
            'uuid' => $uuid,
        ]);
    }
}
