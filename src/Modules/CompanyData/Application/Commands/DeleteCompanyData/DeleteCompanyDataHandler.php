<?php

declare(strict_types=1);

namespace Modules\CompanyData\Application\Commands\DeleteCompanyData;

use Modules\CompanyData\Application\Support\CompanyDataCacheKeys;
use Modules\CompanyData\Domain\Exceptions\CompanyDataNotFoundException;
use Modules\CompanyData\Domain\Ports\CompanyDataAuditPort;
use Modules\CompanyData\Domain\Ports\CompanyDataCachePort;
use Modules\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Modules\CompanyData\Domain\ValueObjects\CompanyDataId;

final readonly class DeleteCompanyDataHandler
{
    public function __construct(
        private CompanyDataRepositoryPort $repository,
        private CompanyDataAuditPort $audit,
        private CompanyDataCachePort $cache,
    ) {
    }

    public function handle(DeleteCompanyDataCommand $command): void
    {
        $id = new CompanyDataId($command->id);
        $companyData = $this->repository->findById($id);

        if (null === $companyData) {
            throw CompanyDataNotFoundException::forId($command->id);
        }

        $this->repository->delete($id);

        // Audit business action
        $this->audit->log(
            logName: 'company.company_data',
            description: 'company_data.deleted',
            properties: ['uuid' => $command->id, 'company_name' => $companyData->companyName],
        );

        $this->cache->forget(CompanyDataCacheKeys::company($command->id));
        $this->cache->forget(CompanyDataCacheKeys::user($companyData->userId->value));
        $this->cache->flushTag(CompanyDataCacheKeys::READ_TAG);
        $this->cache->flushTag(CompanyDataCacheKeys::LIST_TAG);
    }
}
