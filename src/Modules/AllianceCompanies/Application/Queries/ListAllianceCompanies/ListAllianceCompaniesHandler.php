<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Queries\ListAllianceCompanies;

use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Illuminate\Support\Facades\Cache;

final readonly class ListAllianceCompaniesHandler
{
    public function __construct(
        private AllianceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(ListAllianceCompaniesQuery $query): array
    {
        $cacheKey = 'alliance_companies_list_' . md5(serialize($query->filters));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query) {
            return $this->repository->list($query->filters);
        });
    }
}
