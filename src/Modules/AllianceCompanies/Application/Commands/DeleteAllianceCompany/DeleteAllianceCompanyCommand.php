<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands\DeleteAllianceCompany;

final readonly class DeleteAllianceCompanyCommand
{
    public function __construct(
        public string $uuid,
    ) {
    }
}
