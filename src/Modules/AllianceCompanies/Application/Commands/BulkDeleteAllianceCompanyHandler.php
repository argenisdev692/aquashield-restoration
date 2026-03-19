<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Commands;

use Modules\AllianceCompanies\Application\DTOs\BulkDeleteAllianceCompanyData;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;

final class BulkDeleteAllianceCompanyHandler
{
    public function __construct(
        private readonly AllianceCompanyRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteAllianceCompanyData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): AllianceCompanyId => AllianceCompanyId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
