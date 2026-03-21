<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Commands;

use RuntimeException;
use Modules\MortgageCompanies\Application\DTOs\UpdateMortgageCompanyData;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

final class UpdateMortgageCompanyHandler
{
    public function __construct(
        private readonly MortgageCompanyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateMortgageCompanyData $data): void
    {
        $id = MortgageCompanyId::fromString($uuid);
        $mortgageCompany = $this->repository->find($id);

        if ($mortgageCompany === null) {
            throw new RuntimeException('Mortgage company not found.');
        }

        $updated = $mortgageCompany->update(
            mortgageCompanyName: $data->mortgageCompanyName,
            address: $data->address,
            address2: $data->address2,
            phone: $data->phone,
            email: $data->email,
            website: $data->website,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($updated);
    }
}
