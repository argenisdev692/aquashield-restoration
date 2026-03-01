<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands\CreateAllianceCompany;

use Modules\AllianceCompanies\Application\DTOs\AllianceCompanyDTO;

final readonly class CreateAllianceCompanyCommand
{
    public function __construct(
        public AllianceCompanyDTO $dto,
    ) {
    }
}
