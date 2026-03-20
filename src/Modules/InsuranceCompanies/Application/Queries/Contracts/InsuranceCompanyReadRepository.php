<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterData;
use Modules\InsuranceCompanies\Application\Queries\ReadModels\InsuranceCompanyReadModel;

interface InsuranceCompanyReadRepository
{
    public function paginate(InsuranceCompanyFilterData $filters): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?InsuranceCompanyReadModel;
}
