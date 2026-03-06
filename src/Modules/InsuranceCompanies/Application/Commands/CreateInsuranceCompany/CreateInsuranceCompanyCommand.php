<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany;

use Modules\InsuranceCompanies\Application\DTOs\CreateInsuranceCompanyDTO;

final readonly class CreateInsuranceCompanyCommand
{
    public function __construct(
        public CreateInsuranceCompanyDTO $dto,
    ) {
    }
}
