<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands;

use Modules\AllianceCompanies\Application\DTOs\StoreAllianceCompanyData;
use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;

final class CreateAllianceCompanyHandler
{
    public function __construct(
        private readonly AllianceCompanyRepositoryPort $repository,
    ) {}

    public function handle(StoreAllianceCompanyData $data): string
    {
        $id = AllianceCompanyId::generate();
        $allianceCompany = AllianceCompany::create(
            id: $id,
            allianceCompanyName: $data->allianceCompanyName,
            address: $data->address,
            phone: $data->phone,
            email: $data->email,
            website: $data->website,
            userId: $data->userId,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($allianceCompany);

        return $id->toString();
    }
}
