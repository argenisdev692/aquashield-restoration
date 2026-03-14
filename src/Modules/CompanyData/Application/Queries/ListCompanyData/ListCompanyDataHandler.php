<?php

declare(strict_types=1);

namespace Modules\CompanyData\Application\Queries\ListCompanyData;

use Modules\CompanyData\Application\DTOs\CompanyDataFilterDTO;
use Modules\CompanyData\Application\Queries\ReadModels\CompanyDataReadModel;
use Modules\CompanyData\Application\Support\CompanyDataCacheKeys;
use Modules\CompanyData\Domain\Ports\CompanyDataCachePort;
use Modules\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Modules\CompanyData\Domain\Ports\CompanySignatureStoragePort;

final readonly class ListCompanyDataHandler
{
    public function __construct(
        private CompanyDataRepositoryPort $repository,
        private CompanySignatureStoragePort $signatureStorage,
        private CompanyDataCachePort $cache,
    ) {
    }

    /**
     * @return array{data: list<CompanyDataReadModel>, meta: array{total: int, perPage: int, currentPage: int, lastPage: int}}
     */
    public function handle(ListCompanyDataQuery $query): array
    {
        $filters = $query->filters;
        $cacheKey = CompanyDataCacheKeys::list($filters->toArray());
        $ttl = 60 * 15;

        return $this->cache->rememberTagged(
            CompanyDataCacheKeys::LIST_TAG,
            $cacheKey,
            $ttl,
            fn(): array => $this->fetchPaginatedData($filters),
        );
    }

    /**
     * @return array{data: list<CompanyDataReadModel>, meta: array{total: int, perPage: int, currentPage: int, lastPage: int}}
     */
    private function fetchPaginatedData(CompanyDataFilterDTO $filters): array
    {
        $result = $this->repository->findAllPaginated(
            filters: $filters->toArray(),
            page: $filters->page,
            perPage: $filters->perPage,
        );

        $mapped = array_map(
            function ($companyData): CompanyDataReadModel {
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
            },
            $result['data'],
        );

        return [
            'data' => $mapped,
            'meta' => [
                'total' => $result['total'],
                'perPage' => $result['perPage'],
                'currentPage' => $result['currentPage'],
                'lastPage' => $result['lastPage'],
            ],
        ];
    }
}
