<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Commands\DeleteCompanyData;

use Src\Contexts\CompanyData\Domain\Exceptions\CompanyDataNotFoundException;
use Src\Contexts\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Src\Contexts\CompanyData\Domain\ValueObjects\CompanyDataId;
use Illuminate\Support\Facades\Cache;

final class DeleteCompanyDataHandler
{
    public function __construct(
        private readonly CompanyDataRepositoryPort $repository
    ) {
    }

    public function handle(DeleteCompanyDataCommand $command): void
    {
        $id = CompanyDataId::fromString($command->id);
        $companyData = $this->repository->findById($id);

        if (!$companyData) {
            throw new CompanyDataNotFoundException($command->id);
        }

        $this->repository->delete($id);

        Cache::forget("company_data_{$command->id}");
        Cache::forget("company_data_user_{$companyData->userId->value}");
    }
}
