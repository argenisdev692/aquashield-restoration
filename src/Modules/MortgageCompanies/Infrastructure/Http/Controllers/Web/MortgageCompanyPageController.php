<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\MortgageCompanies\Application\Queries\GetMortgageCompany\GetMortgageCompanyHandler;

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

        return Inertia::render('mortgage-companies/MortgageCompanyShowPage', [
            'mortgageCompany' => $mortgageCompany,
        ]);
    }

    public function edit(string $uuid, GetMortgageCompanyHandler $handler): Response
    {
        $mortgageCompany = $handler->handle($uuid);

        return Inertia::render('mortgage-companies/MortgageCompanyEditPage', [
            'mortgageCompany' => $mortgageCompany,
        ]);
    }
}
