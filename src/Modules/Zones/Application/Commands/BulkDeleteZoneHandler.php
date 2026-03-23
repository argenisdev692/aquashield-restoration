<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Application\Commands;

use Src\Modules\Zones\Application\DTOs\BulkDeleteZoneData;
use Src\Modules\Zones\Domain\Ports\ZoneRepositoryPort;
use Src\Modules\Zones\Domain\ValueObjects\ZoneId;

final class BulkDeleteZoneHandler
{
    public function __construct(
        private readonly ZoneRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteZoneData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): ZoneId => ZoneId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
