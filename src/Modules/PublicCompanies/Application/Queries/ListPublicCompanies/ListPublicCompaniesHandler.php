<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Queries\ListPublicCompanies;

use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Illuminate\Support\Facades\Cache;

final readonly class ListPublicCompaniesHandler
{
    public function __construct(
        private PublicCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(ListPublicCompaniesQuery $query): array
    {
        $cacheKey = 'public_companies_list_' . md5(serialize($query->filters));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query) {
            return $this->repository->list($query->filters);
        });
    }
}
