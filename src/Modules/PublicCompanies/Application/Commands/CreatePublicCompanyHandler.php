<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands;

use Modules\PublicCompanies\Application\DTOs\StorePublicCompanyData;
use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;

final class CreatePublicCompanyHandler
{
    public function __construct(
        private readonly PublicCompanyRepositoryPort $repository,
    ) {}

    #[\NoDiscard]
    public function handle(StorePublicCompanyData $data): string
    {
        $publicCompany = PublicCompany::create(
            id: PublicCompanyId::generate(),
            publicCompanyName: $data->publicCompanyName,
            address: $data->address,
            address2: $data->address2,
            phone: $data->phone,
            email: $data->email,
            website: $data->website,
            unit: $data->unit,
            userId: $data->userId,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($publicCompany);

        return $publicCompany->id()->toString();
    }
}
