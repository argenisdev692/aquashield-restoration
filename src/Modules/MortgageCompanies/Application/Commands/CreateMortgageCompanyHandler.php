<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Commands;

use Modules\MortgageCompanies\Application\DTOs\StoreMortgageCompanyData;
use Modules\MortgageCompanies\Domain\Entities\MortgageCompany;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

final class CreateMortgageCompanyHandler
{
    public function __construct(
        private readonly MortgageCompanyRepositoryPort $repository,
    ) {}

    #[\NoDiscard('The generated UUID must be captured')]
    public function handle(StoreMortgageCompanyData $data): string
    {
        $id = MortgageCompanyId::generate();

        $mortgageCompany = MortgageCompany::create(
            id: $id,
            mortgageCompanyName: $data->mortgageCompanyName,
            address: $data->address,
            address2: $data->address2,
            phone: $data->phone,
            email: $data->email,
            website: $data->website,
            userId: $data->userId,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($mortgageCompany);

        return $id->toString();
    }
}
