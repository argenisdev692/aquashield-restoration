<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Controllers\Web;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\DocumentTemplateAlliances\Application\Queries\GetDocumentTemplateAllianceHandler;

final class DocumentTemplateAlliancePageController
{
    public function index(): Response
    {
        return Inertia::render('document-template-alliances/DocumentTemplateAlliancesIndexPage');
    }

    public function show(string $uuid, GetDocumentTemplateAllianceHandler $handler): Response
    {
        $item = $handler->handle($uuid);

        if ($item === null) {
            abort(404);
        }

        return Inertia::render('document-template-alliances/DocumentTemplateAllianceShowPage', [
            'documentTemplateAlliance' => $item,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('document-template-alliances/DocumentTemplateAllianceCreatePage');
    }

    public function edit(string $uuid, GetDocumentTemplateAllianceHandler $handler): Response
    {
        $item = $handler->handle($uuid);

        if ($item === null) {
            abort(404);
        }

        return Inertia::render('document-template-alliances/DocumentTemplateAllianceEditPage', [
            'documentTemplateAlliance' => $item,
        ]);
    }
}
