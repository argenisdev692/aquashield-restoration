<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Queries\GetCompanyData;

use Src\Contexts\CompanyData\Application\DTOs\CompanyDataDTO;
use Src\Contexts\CompanyData\Domain\Exceptions\CompanyDataNotFoundException;
use Src\Contexts\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Src\Contexts\CompanyData\Domain\ValueObjects\CompanyDataId;
use Src\Contexts\CompanyData\Domain\ValueObjects\UserId;
use Illuminate\Support\Facades\Cache;

final class GetCompanyDataHandler
{
    public function __construct(
        private readonly CompanyDataRepositoryPort $repository
    ) {
    }

    public function handle(GetCompanyDataQuery $query): CompanyDataDTO
    {
        $cacheKey = "company_data_" . ($query->id ?? "user_" . $query->userId);
        $ttl = now()->addMinutes(15);

        $companyData = Cache::remember($cacheKey, $ttl, function () use ($query) {
            if ($query->id) {
                return $this->repository->findById(CompanyDataId::fromString($query->id));
            } elseif ($query->userId) {
                return $this->repository->findByUserId(UserId::fromInt($query->userId));
            }
            return null;
        });

        if (!$companyData) {
            throw new CompanyDataNotFoundException($query->id ?? (string) $query->userId);
        }

        return CompanyDataDTO::fromEntity($companyData);
    }
}
