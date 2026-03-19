<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands;

use RuntimeException;
use Modules\AllianceCompanies\Application\DTOs\UpdateAllianceCompanyData;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;

final class UpdateAllianceCompanyHandler
{
    public function __construct(
        private readonly AllianceCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateAllianceCompanyData $data): void
    {
        $allianceCompanyId = AllianceCompanyId::fromString($uuid);
        $allianceCompany = $this->repository->find($allianceCompanyId);

        if ($allianceCompany === null) {
            throw new RuntimeException('Alliance company not found.');
        }

        $allianceCompany->update(
            allianceCompanyName: $data->allianceCompanyName,
            address: $data->address,
            phone: $data->phone,
            email: $data->email,
            website: $data->website,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($allianceCompany);
    }
}
