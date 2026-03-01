<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands\RestoreAllianceCompany;

final readonly class RestoreAllianceCompanyCommand
{
    public function __construct(
        public string $uuid,
    ) {
    }
}
