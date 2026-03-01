<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\ListInsuranceCompanies;

use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Illuminate\Support\Facades\Cache;

final readonly class ListInsuranceCompaniesHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(ListInsuranceCompaniesQuery $query): array
    {
        $cacheKey = 'insurance_companies_list_' . md5(serialize($query->filters));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query) {
            return $this->repository->list($query->filters);
        });
    }
}
