<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Queries\GetAllianceCompany;

use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Illuminate\Support\Facades\Cache;

final readonly class GetAllianceCompanyHandler
{
    public function __construct(
        private AllianceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(GetAllianceCompanyQuery $query): AllianceCompany
    {
        $cacheKey = "alliance_company_{$query->uuid}";

        $AllianceCompany = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query) {
            return $this->repository->findByUuid($query->uuid);
        });

        if (!$AllianceCompany) {
            throw new EntityNotFoundException("Alliance Company with UUID {$query->uuid} not found.");
        }

        return $AllianceCompany;
    }
}
