<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany;

use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyDTO;

final readonly class CreateInsuranceCompanyCommand
{
    public function __construct(
        public InsuranceCompanyDTO $dto,
    ) {
    }
}
