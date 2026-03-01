<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Commands\UpdateMortgageCompany;

use Illuminate\Support\Facades\Cache;
use Modules\MortgageCompanies\Application\DTOs\UpdateMortgageCompanyDTO;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;
use Src\Shared\Domain\Exceptions\EntityNotFoundException;

final readonly class UpdateMortgageCompanyHandler
{
    public function __construct(
        private MortgageCompanyRepositoryPort $repository
    ) {
    }

    public function handle(string $uuid, UpdateMortgageCompanyDTO $dto): void
    {
        $id = MortgageCompanyId::fromString($uuid);
        $mortgageCompany = $this->repository->find($id);

        if (!$mortgageCompany) {
            throw new EntityNotFoundException("Mortgage company not found: {$uuid}");
        }

        $mortgageCompany->update(
            mortgageCompanyName: $dto->mortgageCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website,
        );

        $this->repository->save($mortgageCompany);

        Cache::forget("mortgage_company_{$uuid}");
        try {
            Cache::tags(['mortgage_companies_list'])->flush();
        } catch (\Exception $e) {
            // Tags not supported
        }
    }
}
