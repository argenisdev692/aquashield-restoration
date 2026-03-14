<?php

declare(strict_types=1);

namespace Modules\CompanyData\Application\Commands\RestoreCompanyData;

use Modules\CompanyData\Application\Support\CompanyDataCacheKeys;
use Modules\CompanyData\Domain\Exceptions\CompanyDataNotFoundException;
use Modules\CompanyData\Domain\Ports\CompanyDataAuditPort;
use Modules\CompanyData\Domain\Ports\CompanyDataCachePort;
use Modules\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Modules\CompanyData\Domain\ValueObjects\CompanyDataId;

final readonly class RestoreCompanyDataHandler
{
    public function __construct(
        private CompanyDataRepositoryPort $repository,
        private CompanyDataAuditPort $audit,
        private CompanyDataCachePort $cache,
    ) {
    }

    public function handle(RestoreCompanyDataCommand $command): void
    {
        $id = new CompanyDataId($command->id);
        $companyData = $this->repository->findById($id);

        if (null === $companyData) {
            throw CompanyDataNotFoundException::forId($command->id);
        }

        $this->repository->restore($id);

        // Audit business action
        $this->audit->log(
            logName: 'company.company_data',
            description: 'company_data.restored',
            properties: ['uuid' => $command->id],
        );

        $this->cache->forget(CompanyDataCacheKeys::company($companyData->id->value));
        $this->cache->forget(CompanyDataCacheKeys::user($companyData->userId->value));
        $this->cache->flushTag(CompanyDataCacheKeys::READ_TAG);
        $this->cache->flushTag(CompanyDataCacheKeys::LIST_TAG);
    }
}
