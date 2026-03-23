<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Src\Modules\DocumentTemplateAdjusters\Application\Queries\GetDocumentTemplateAdjusterHandler;

final class DocumentTemplateAdjusterPageController extends Controller
{
    public function __construct(
        private readonly GetDocumentTemplateAdjusterHandler $getHandler,
    ) {}

    public function index(): Response
    {
        return Inertia::render('document-template-adjusters/DocumentTemplateAdjusterIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('document-template-adjusters/DocumentTemplateAdjusterCreatePage');
    }

    public function show(string $uuid): Response
    {
        $dto = $this->getHandler->handle($uuid);

        if ($dto === null) {
            throw new NotFoundHttpException('Document template adjuster not found.');
        }

        return Inertia::render('document-template-adjusters/DocumentTemplateAdjusterShowPage', [
            'uuid'                      => $uuid,
            'documentTemplateAdjuster'  => $dto,
        ]);
    }

    public function edit(string $uuid): Response
    {
        $dto = $this->getHandler->handle($uuid);

        if ($dto === null) {
            throw new NotFoundHttpException('Document template adjuster not found.');
        }

        return Inertia::render('document-template-adjusters/DocumentTemplateAdjusterEditPage', [
            'uuid'                      => $uuid,
            'documentTemplateAdjuster'  => $dto,
        ]);
    }
}
