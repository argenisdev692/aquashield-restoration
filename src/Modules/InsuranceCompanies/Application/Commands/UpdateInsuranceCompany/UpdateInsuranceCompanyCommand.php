<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompany;

use Modules\InsuranceCompanies\Application\DTOs\UpdateInsuranceCompanyDTO;

final readonly class UpdateInsuranceCompanyCommand
{
    public function __construct(
        public string $uuid,
        public UpdateInsuranceCompanyDTO $dto,
    ) {
    }
}
