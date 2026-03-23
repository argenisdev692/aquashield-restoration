<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Application\Commands;

use RuntimeException;
use Src\Modules\Zones\Application\DTOs\UpdateZoneData;
use Src\Modules\Zones\Domain\Ports\ZoneRepositoryPort;
use Src\Modules\Zones\Domain\ValueObjects\ZoneId;

final class UpdateZoneHandler
{
    public function __construct(
        private readonly ZoneRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateZoneData $data): void
    {
        $id   = ZoneId::fromString($uuid);
        $zone = $this->repository->find($id);

        if ($zone === null) {
            throw new RuntimeException('Zone not found.');
        }

        $zone->update(
            zoneName: $data->zoneName,
            zoneType: $data->zoneType,
            code: $data->code,
            description: $data->description,
            userId: $data->userId,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($zone);
    }
}
