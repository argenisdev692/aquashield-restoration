<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Commands\UpdateCompanyData;

use Src\Contexts\CompanyData\Domain\Entities\CompanyData;
use Src\Contexts\CompanyData\Domain\Exceptions\CompanyDataNotFoundException;
use Src\Contexts\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Src\Contexts\CompanyData\Domain\ValueObjects\CompanyDataId;
use Illuminate\Support\Facades\Cache;

final class UpdateCompanyDataHandler
{
    public function __construct(
        private readonly CompanyDataRepositoryPort $repository
    ) {
    }

    public function handle(UpdateCompanyDataCommand $command): void
    {
        $id = CompanyDataId::fromString($command->id);
        $companyData = $this->repository->findById($id);

        if (!$companyData) {
            throw new CompanyDataNotFoundException($command->id);
        }

        $dto = $command->dto;

        $updatedCompanyData = new CompanyData(
            id: $companyData->id,
            userId: $companyData->userId,
            name: $dto->name ?? $companyData->name,
            companyName: $dto->companyName ?? $companyData->companyName,
            email: $dto->email ?? $companyData->email,
            phone: $dto->phone ?? $companyData->phone,
            address: $dto->address ?? $companyData->address,
            website: $dto->website ?? $companyData->website,
            facebookLink: $dto->facebookLink ?? $companyData->facebookLink,
            instagramLink: $dto->instagramLink ?? $companyData->instagramLink,
            linkedinLink: $dto->linkedinLink ?? $companyData->linkedinLink,
            twitterLink: $dto->twitterLink ?? $companyData->twitterLink,
            latitude: $dto->latitude ?? $companyData->latitude,
            longitude: $dto->longitude ?? $companyData->longitude,
            signaturePath: $dto->signaturePath ?? $companyData->signaturePath,
            createdAt: $companyData->createdAt,
        );

        $this->repository->save($updatedCompanyData);

        // Invalidate single entity caches and all list caches
        // Note: For simple list caching without Redis tags, you might need a more sophisticated invalidation strategy, 
        // or accept stale list caches. Here we just invalidate the specific entity.
        Cache::forget("company_data_{$command->id}");
        Cache::forget("company_data_user_{$companyData->userId->value}");

        // Consider implementing Redis Tags for invalidating 'company_data_list_*' completely 
        // Example: Cache::tags(['company_data_list'])->flush();
    }
}
