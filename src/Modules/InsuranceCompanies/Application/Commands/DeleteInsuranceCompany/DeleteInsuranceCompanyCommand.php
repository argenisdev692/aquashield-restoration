<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\DeleteInsuranceCompany;

final readonly class DeleteInsuranceCompanyCommand
{
    public function __construct(
        public string $uuid,
    ) {
    }
}
