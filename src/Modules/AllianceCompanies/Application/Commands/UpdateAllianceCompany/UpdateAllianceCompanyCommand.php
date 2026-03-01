<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands\UpdateAllianceCompany;

use Modules\AllianceCompanies\Application\DTOs\AllianceCompanyDTO;

final readonly class UpdateAllianceCompanyCommand
{
    public function __construct(
        public string $uuid,
        public AllianceCompanyDTO $dto,
    ) {
    }
}
