<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Queries\GetPublicCompany;

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Illuminate\Support\Facades\Cache;

final readonly class GetPublicCompanyHandler
{
    public function __construct(
        private PublicCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(GetPublicCompanyQuery $query): PublicCompany
    {
        $cacheKey = "public_company_{$query->uuid}";

        $PublicCompany = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query) {
            return $this->repository->findByUuid($query->uuid);
        });

        if (!$PublicCompany) {
            throw new EntityNotFoundException("Public Company with UUID {$query->uuid} not found.");
        }

        return $PublicCompany;
    }
}
