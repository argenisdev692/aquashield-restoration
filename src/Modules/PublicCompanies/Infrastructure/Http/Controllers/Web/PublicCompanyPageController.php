<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\PublicCompanies\Application\Queries\GetPublicCompanyHandler;

final class PublicCompanyPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public-companies/PublicCompaniesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('public-companies/PublicCompanyCreatePage');
    }

    public function show(string $uuid, GetPublicCompanyHandler $handler): Response
    {
        $publicCompany = $handler->handle($uuid);

        if ($publicCompany === null) {
            abort(404);
        }

        return Inertia::render('public-companies/PublicCompanyShowPage', [
            'publicCompany' => [
                'data' => $publicCompany,
            ],
        ]);
    }

    public function edit(string $uuid, GetPublicCompanyHandler $handler): Response
    {
        $publicCompany = $handler->handle($uuid);

        if ($publicCompany === null) {
            abort(404);
        }

        return Inertia::render('public-companies/PublicCompanyEditPage', [
            'publicCompany' => [
                'data' => $publicCompany,
            ],
        ]);
    }
}
