<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands\RestorePublicCompany;

final readonly class RestorePublicCompanyCommand
{
    public function __construct(
        public string $uuid,
    ) {
    }
}
