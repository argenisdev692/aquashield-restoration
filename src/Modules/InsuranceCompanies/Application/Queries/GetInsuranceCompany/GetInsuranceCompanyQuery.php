<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompany;

final readonly class GetInsuranceCompanyQuery
{
    public function __construct(
        public string $uuid,
    ) {
    }
}
