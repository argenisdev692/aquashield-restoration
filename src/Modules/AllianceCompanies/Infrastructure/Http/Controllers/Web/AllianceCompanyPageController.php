<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\AllianceCompanies\Application\Queries\GetAllianceCompanyHandler;

final class AllianceCompanyPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('alliance-companies/AllianceCompaniesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('alliance-companies/AllianceCompanyCreatePage');
    }

    public function show(string $uuid, GetAllianceCompanyHandler $handler): Response
    {
        $allianceCompany = $handler->handle($uuid);

        if ($allianceCompany === null) {
            abort(404);
        }

        return Inertia::render('alliance-companies/AllianceCompanyShowPage', [
            'allianceCompany' => $allianceCompany->toArray(),
        ]);
    }

    public function edit(string $uuid, GetAllianceCompanyHandler $handler): Response
    {
        $allianceCompany = $handler->handle($uuid);

        if ($allianceCompany === null) {
            abort(404);
        }

        return Inertia::render('alliance-companies/AllianceCompanyEditPage', [
            'allianceCompany' => $allianceCompany->toArray(),
        ]);
    }
}
