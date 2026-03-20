<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompanyHandler;

final class InsuranceCompanyPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('insurance-companies/InsuranceCompaniesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('insurance-companies/InsuranceCompanyCreatePage');
    }

    public function show(string $uuid, GetInsuranceCompanyHandler $handler): Response
    {
        $insuranceCompany = $handler->handle($uuid);

        if ($insuranceCompany === null) {
            abort(404);
        }

        return Inertia::render('insurance-companies/InsuranceCompanyShowPage', [
            'insuranceCompany' => [
                'data' => $insuranceCompany,
            ],
        ]);
    }

    public function edit(string $uuid, GetInsuranceCompanyHandler $handler): Response
    {
        $insuranceCompany = $handler->handle($uuid);

        if ($insuranceCompany === null) {
            abort(404);
        }

        return Inertia::render('insurance-companies/InsuranceCompanyEditPage', [
            'insuranceCompany' => [
                'data' => $insuranceCompany,
            ],
        ]);
    }
}
