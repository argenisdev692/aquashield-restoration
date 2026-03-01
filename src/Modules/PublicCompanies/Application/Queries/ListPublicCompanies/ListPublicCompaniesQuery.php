<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Queries\ListPublicCompanies;

final readonly class ListPublicCompaniesQuery
{
    public function __construct(
        public array $filters = [],
    ) {
    }
}
