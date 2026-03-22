<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Application\Commands;

use Src\Modules\ClaimStatuses\Application\DTOs\BulkDeleteClaimStatusData;
use Src\Modules\ClaimStatuses\Domain\Ports\ClaimStatusRepositoryPort;
use Src\Modules\ClaimStatuses\Domain\ValueObjects\ClaimStatusId;

final class BulkDeleteClaimStatusHandler
{
    public function __construct(
        private readonly ClaimStatusRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteClaimStatusData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): ClaimStatusId => ClaimStatusId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
