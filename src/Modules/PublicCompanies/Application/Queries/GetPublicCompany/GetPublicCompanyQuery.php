<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Queries\GetPublicCompany;

final readonly class GetPublicCompanyQuery
{
    public function __construct(
        public string $uuid,
    ) {
    }
}
