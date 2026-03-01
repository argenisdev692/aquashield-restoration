<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Web;

use Inertia\Inertia;
use Inertia\Response;
use Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompany\GetInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompany\GetInsuranceCompanyQuery;
use Modules\InsuranceCompanies\Infrastructure\Http\Resources\InsuranceCompanyResource;

final class InsuranceCompanyPageController
{
    public function __construct(
        private readonly GetInsuranceCompanyHandler $getHandler,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('insurance-companies/InsuranceCompaniesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('insurance-companies/InsuranceCompanyCreatePage');
    }

    public function show(string $uuid): Response
    {
        $insuranceCompany = $this->getHandler->handle(new GetInsuranceCompanyQuery($uuid));

        return Inertia::render('insurance-companies/InsuranceCompanyShowPage', [
            'insuranceCompany' => new InsuranceCompanyResource($insuranceCompany),
        ]);
    }

    public function edit(string $uuid): Response
    {
        $insuranceCompany = $this->getHandler->handle(new GetInsuranceCompanyQuery($uuid));

        return Inertia::render('insurance-companies/InsuranceCompanyEditPage', [
            'insuranceCompany' => new InsuranceCompanyResource($insuranceCompany),
        ]);
    }
}
