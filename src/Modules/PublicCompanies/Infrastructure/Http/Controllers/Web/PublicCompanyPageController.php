<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Controllers\Web;

use Inertia\Inertia;
use Inertia\Response;
use Modules\PublicCompanies\Application\Queries\GetPublicCompany\GetPublicCompanyHandler;
use Modules\PublicCompanies\Application\Queries\GetPublicCompany\GetPublicCompanyQuery;
use Modules\PublicCompanies\Infrastructure\Http\Resources\PublicCompanyResource;

final class PublicCompanyPageController
{
    public function __construct(
        private readonly GetPublicCompanyHandler $getHandler,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('public-companies/PublicCompaniesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('public-companies/PublicCompanyCreatePage');
    }

    public function show(string $uuid): Response
    {
        $PublicCompany = $this->getHandler->handle(new GetPublicCompanyQuery($uuid));

        return Inertia::render('public-companies/PublicCompanyShowPage', [
            'PublicCompany' => new PublicCompanyResource($PublicCompany),
        ]);
    }

    public function edit(string $uuid): Response
    {
        $PublicCompany = $this->getHandler->handle(new GetPublicCompanyQuery($uuid));

        return Inertia::render('public-companies/PublicCompanyEditPage', [
            'PublicCompany' => new PublicCompanyResource($PublicCompany),
        ]);
    }
}
