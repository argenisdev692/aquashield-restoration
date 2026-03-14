<?php

declare(strict_types=1);

namespace Modules\CompanyData\Application\Queries\GetCompanyData;

use Modules\CompanyData\Application\Queries\ReadModels\CompanyDataReadModel;
use Modules\CompanyData\Application\Support\CompanyDataCacheKeys;
use Modules\CompanyData\Domain\Exceptions\CompanyDataNotFoundException;
use Modules\CompanyData\Domain\Ports\CompanyDataCachePort;
use Modules\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Modules\CompanyData\Domain\Ports\CompanySignatureStoragePort;
use Modules\CompanyData\Domain\ValueObjects\CompanyDataId;
use Modules\CompanyData\Domain\ValueObjects\UserId;

final readonly class GetCompanyDataHandler
{
    public function __construct(
        private CompanyDataRepositoryPort $repository,
        private CompanySignatureStoragePort $signatureStorage,
        private CompanyDataCachePort $cache,
    ) {
    }

    public function handle(GetCompanyDataQuery $query): CompanyDataReadModel
    {
        $cacheKey = $query->companyUuid !== null
            ? CompanyDataCacheKeys::company($query->companyUuid)
            : CompanyDataCacheKeys::user((string) $query->userUuid);

        $ttl = 60 * 60; // 1 hour

        return $this->cache->rememberTagged(
            CompanyDataCacheKeys::READ_TAG,
            $cacheKey,
            $ttl,
            fn(): CompanyDataReadModel => $this->fetchReadModel($query),
        );
    }

    private function fetchReadModel(GetCompanyDataQuery $query): CompanyDataReadModel
    {
        $companyData = $query->companyUuid !== null
            ? $this->repository->findById(new CompanyDataId($query->companyUuid))
            : $this->repository->findByUserId(new UserId((string) $query->userUuid));

        if (null === $companyData) {
            throw $query->companyUuid !== null
                ? CompanyDataNotFoundException::forId($query->companyUuid)
                : CompanyDataNotFoundException::forUser((string) $query->userUuid);
        }

        $socialLinks = $companyData->socialLinks->toArray();
        $coordinates = $companyData->coordinates->toArray();

        return new CompanyDataReadModel(
            uuid: $companyData->id->value,
            userUuid: $companyData->userId->value,
            companyName: $companyData->companyName,
            name: $companyData->name,
            email: $companyData->email,
            phone: $companyData->phone,
            address: $companyData->address,
            address2: $companyData->address2,
            website: $socialLinks['website'] ?? null,
            facebookLink: $socialLinks['facebook'] ?? null,
            instagramLink: $socialLinks['instagram'] ?? null,
            linkedinLink: $socialLinks['linkedin'] ?? null,
            twitterLink: $socialLinks['twitter'] ?? null,
            socialLinks: $socialLinks,
            coordinates: $coordinates,
            latitude: $coordinates['latitude'],
            longitude: $coordinates['longitude'],
            status: $companyData->status->value,
            signatureUrl: $this->signatureStorage->url($companyData->signaturePath),
            createdAt: $companyData->createdAt ?? '',
            updatedAt: $companyData->updatedAt ?? '',
            deletedAt: $companyData->deletedAt,
        );
    }
}
