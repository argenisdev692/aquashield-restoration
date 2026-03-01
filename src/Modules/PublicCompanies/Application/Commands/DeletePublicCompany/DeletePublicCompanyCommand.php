<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands\DeletePublicCompany;

final readonly class DeletePublicCompanyCommand
{
    public function __construct(
        public string $uuid,
    ) {
    }
}
