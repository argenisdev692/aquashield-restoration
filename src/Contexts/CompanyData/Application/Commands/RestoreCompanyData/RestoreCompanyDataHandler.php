<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Commands\RestoreCompanyData;

use Src\Contexts\CompanyData\Domain\Exceptions\CompanyDataNotFoundException;
use Src\Contexts\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Src\Contexts\CompanyData\Domain\ValueObjects\CompanyDataId;
use Illuminate\Support\Facades\Cache;

final class RestoreCompanyDataHandler
{
    public function __construct(
        private readonly CompanyDataRepositoryPort $repository
    ) {
    }

    public function handle(RestoreCompanyDataCommand $command): void
    {
        $id = CompanyDataId::fromString($command->id);

        if (!method_exists($this->repository, 'restore')) {
            throw new \RuntimeException("Repository does not support restore operation.");
        }

        $this->repository->restore($id);

        Cache::forget("company_data_{$command->id}");
    }
}
