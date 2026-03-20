<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterData;
use Modules\InsuranceCompanies\Application\Queries\Contracts\InsuranceCompanyReadRepository;

final class ListInsuranceCompaniesHandler
{
    public function __construct(
        private readonly InsuranceCompanyReadRepository $repository,
    ) {
    }

    public function handle(InsuranceCompanyFilterData $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }
}
