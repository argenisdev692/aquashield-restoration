<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Commands\CreateMortgageCompany;

use Illuminate\Support\Facades\Cache;
use Modules\MortgageCompanies\Application\DTOs\CreateMortgageCompanyDTO;
use Modules\MortgageCompanies\Domain\Entities\MortgageCompany;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

final readonly class CreateMortgageCompanyHandler
{
    public function __construct(
        private MortgageCompanyRepositoryPort $repository
    ) {
    }

    public function handle(CreateMortgageCompanyDTO $dto): string
    {
        $id = MortgageCompanyId::generate();

        $mortgageCompany = MortgageCompany::create(
            id: $id,
            mortgageCompanyName: $dto->mortgageCompanyName,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            website: $dto->website,
            userId: $dto->userId,
        );

        $this->repository->save($mortgageCompany);

        try {
            Cache::tags(['mortgage_companies_list'])->flush();
        } catch (\Exception $e) {
            // Tags not supported, cache will expire naturally
        }

        return $id->toString();
    }
}
