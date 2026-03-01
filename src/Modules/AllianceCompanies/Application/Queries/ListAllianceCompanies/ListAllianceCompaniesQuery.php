<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Queries\ListAllianceCompanies;

final readonly class ListAllianceCompaniesQuery
{
    public function __construct(
        public array $filters = [],
    ) {
    }
}
