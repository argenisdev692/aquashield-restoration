<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Commands\RestoreCompanyData;

final readonly class RestoreCompanyDataCommand
{
    public function __construct(
        public string $id
    ) {
    }
}
