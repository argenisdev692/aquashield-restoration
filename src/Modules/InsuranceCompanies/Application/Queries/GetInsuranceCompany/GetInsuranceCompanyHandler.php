<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompany;

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Illuminate\Support\Facades\Cache;

final readonly class GetInsuranceCompanyHandler
{
    public function __construct(
        private InsuranceCompanyRepositoryPort $repository,
    ) {
    }

    public function handle(GetInsuranceCompanyQuery $query): InsuranceCompany
    {
        $cacheKey = "insurance_company_{$query->uuid}";

        $insuranceCompany = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query) {
            return $this->repository->findByUuid($query->uuid);
        });

        if (!$insuranceCompany) {
            throw new EntityNotFoundException("Insurance Company with UUID {$query->uuid} not found.");
        }

        return $insuranceCompany;
    }
}
