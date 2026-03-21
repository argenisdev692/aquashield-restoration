<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\MortgageCompanies\Application\Queries\GetMortgageCompanyHandler;

final class MortgageCompanyPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('mortgage-companies/MortgageCompaniesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('mortgage-companies/MortgageCompanyCreatePage');
    }

    public function show(string $uuid, GetMortgageCompanyHandler $handler): Response
    {
        $mortgageCompany = $handler->handle($uuid);

        if ($mortgageCompany === null) {
            abort(404);
        }

        return Inertia::render('mortgage-companies/MortgageCompanyShowPage', [
            'mortgageCompany' => $mortgageCompany->toArray(),
        ]);
    }

    public function edit(string $uuid, GetMortgageCompanyHandler $handler): Response
    {
        $mortgageCompany = $handler->handle($uuid);

        if ($mortgageCompany === null) {
            abort(404);
        }

        return Inertia::render('mortgage-companies/MortgageCompanyEditPage', [
            'mortgageCompany' => $mortgageCompany->toArray(),
        ]);
    }
}
