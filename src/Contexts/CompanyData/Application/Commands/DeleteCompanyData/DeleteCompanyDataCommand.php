<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Commands\DeleteCompanyData;

final readonly class DeleteCompanyDataCommand
{
    public function __construct(
        public string $id
    ) {
    }
}
