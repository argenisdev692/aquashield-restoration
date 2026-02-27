<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Queries\ListCompanyData;

use Src\Contexts\CompanyData\Application\DTOs\CompanyDataFilterDTO;

final readonly class ListCompanyDataQuery
{
    public function __construct(
        public CompanyDataFilterDTO $filters
    ) {
    }
}
