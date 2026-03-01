<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Queries\GetAllianceCompany;

final readonly class GetAllianceCompanyQuery
{
    public function __construct(
        public string $uuid,
    ) {
    }
}
