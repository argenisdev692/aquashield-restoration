<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\RestoreInsuranceCompany;

final readonly class RestoreInsuranceCompanyCommand
{
    public function __construct(
        public string $uuid,
    ) {
    }
}
