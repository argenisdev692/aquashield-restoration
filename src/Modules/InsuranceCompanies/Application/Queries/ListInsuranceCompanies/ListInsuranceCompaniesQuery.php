<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\ListInsuranceCompanies;

final readonly class ListInsuranceCompaniesQuery
{
    public function __construct(
        public array $filters = [],
    ) {
    }
}
