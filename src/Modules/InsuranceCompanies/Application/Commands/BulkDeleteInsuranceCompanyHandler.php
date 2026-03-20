<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Commands;

use Modules\InsuranceCompanies\Application\DTOs\BulkDeleteInsuranceCompanyData;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

final class BulkDeleteInsuranceCompanyHandler
{
    public function __construct(
        private readonly InsuranceCompanyRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteInsuranceCompanyData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): InsuranceCompanyId => InsuranceCompanyId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
