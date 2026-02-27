<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Queries\ListCompanyData;

use Src\Contexts\CompanyData\Application\DTOs\CompanyDataDTO;
use Src\Contexts\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Illuminate\Support\Facades\Cache;

final class ListCompanyDataHandler
{
    public function __construct(
        private readonly CompanyDataRepositoryPort $repository
    ) {
    }

    public function handle(ListCompanyDataQuery $query): array
    {
        // MD5 of serialized filters avoids cache key too long errors and uniquely caches the page/query state
        $cacheKey = "company_data_list_" . md5(serialize($query->filters));
        $ttl = now()->addMinutes(15);

        return Cache::remember($cacheKey, $ttl, function () use ($query) {
            return $this->repository->paginate($query->filters);
        });
    }
}
