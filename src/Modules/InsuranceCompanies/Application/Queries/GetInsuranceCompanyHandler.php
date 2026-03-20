<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries;

use Modules\InsuranceCompanies\Application\Queries\Contracts\InsuranceCompanyReadRepository;
use Modules\InsuranceCompanies\Application\Queries\ReadModels\InsuranceCompanyReadModel;

final class GetInsuranceCompanyHandler
{
    public function __construct(
        private readonly InsuranceCompanyReadRepository $repository,
    ) {
    }

    public function handle(string $uuid): ?InsuranceCompanyReadModel
    {
        return $this->repository->findByUuid($uuid);
    }
}
