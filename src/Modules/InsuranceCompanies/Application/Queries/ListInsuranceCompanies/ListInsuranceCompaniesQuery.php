<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\ListInsuranceCompanies;

use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterDTO;

final readonly class ListInsuranceCompaniesQuery
{
    public function __construct(
        public InsuranceCompanyFilterDTO $filters,
    ) {
    }
}
