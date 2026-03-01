<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Http\Controllers\Web;

use Inertia\Inertia;
use Inertia\Response;
use Modules\AllianceCompanies\Application\Queries\GetAllianceCompany\GetAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Queries\GetAllianceCompany\GetAllianceCompanyQuery;
use Modules\AllianceCompanies\Infrastructure\Http\Resources\AllianceCompanyResource;

final class AllianceCompanyPageController
{
    public function __construct(
        private readonly GetAllianceCompanyHandler $getHandler,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('alliance-companies/AllianceCompaniesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('alliance-companies/AllianceCompanyCreatePage');
    }

    public function show(string $uuid): Response
    {
        $AllianceCompany = $this->getHandler->handle(new GetAllianceCompanyQuery($uuid));

        return Inertia::render('alliance-companies/AllianceCompanyShowPage', [
            'AllianceCompany' => new AllianceCompanyResource($AllianceCompany),
        ]);
    }

    public function edit(string $uuid): Response
    {
        $AllianceCompany = $this->getHandler->handle(new GetAllianceCompanyQuery($uuid));

        return Inertia::render('alliance-companies/AllianceCompanyEditPage', [
            'AllianceCompany' => new AllianceCompanyResource($AllianceCompany),
        ]);
    }
}
