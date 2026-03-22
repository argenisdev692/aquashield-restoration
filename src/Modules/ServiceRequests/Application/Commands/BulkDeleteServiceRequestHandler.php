<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Application\Commands;

use Src\Modules\ServiceRequests\Application\DTOs\BulkDeleteServiceRequestData;
use Src\Modules\ServiceRequests\Domain\Ports\ServiceRequestRepositoryPort;
use Src\Modules\ServiceRequests\Domain\ValueObjects\ServiceRequestId;

final class BulkDeleteServiceRequestHandler
{
    public function __construct(
        private readonly ServiceRequestRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteServiceRequestData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): ServiceRequestId => ServiceRequestId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
