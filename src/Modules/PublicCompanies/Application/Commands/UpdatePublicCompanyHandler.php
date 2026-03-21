<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands;

use Modules\PublicCompanies\Application\DTOs\UpdatePublicCompanyData;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use RuntimeException;

final class UpdatePublicCompanyHandler
{
    public function __construct(
        private readonly PublicCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdatePublicCompanyData $data): void
    {
        $publicCompany = $this->repository->find(PublicCompanyId::fromString($uuid));

        if ($publicCompany === null) {
            throw new RuntimeException('Public company not found.');
        }

        $publicCompany->update(
            publicCompanyName: $data->publicCompanyName,
            address: $data->address,
            address2: $data->address2,
            phone: $data->phone,
            email: $data->email,
            website: $data->website,
            unit: $data->unit,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($publicCompany);
    }
}
