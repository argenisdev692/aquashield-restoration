<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Queries\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterData;
use Modules\MortgageCompanies\Application\Queries\ReadModels\MortgageCompanyReadModel;

interface MortgageCompanyReadRepository
{
    public function paginate(MortgageCompanyFilterData $filters): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?MortgageCompanyReadModel;
}
