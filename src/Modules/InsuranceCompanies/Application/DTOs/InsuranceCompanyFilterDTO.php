<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\DTOs;

use Spatie\LaravelData\Data;

class InsuranceCompanyFilterDTO extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?string $status = null,
        public ?string $onlyTrashed = null,
        public ?string $sortBy = 'created_at',
        public ?string $sortDir = 'desc',
        public int $page = 1,
        public int $perPage = 15,
    ) {
    }
}
