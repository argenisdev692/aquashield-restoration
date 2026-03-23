<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Application\Commands;

use Src\Modules\Zones\Application\DTOs\StoreZoneData;
use Src\Modules\Zones\Domain\Entities\Zone;
use Src\Modules\Zones\Domain\Ports\ZoneRepositoryPort;
use Src\Modules\Zones\Domain\ValueObjects\ZoneId;

final class CreateZoneHandler
{
    public function __construct(
        private readonly ZoneRepositoryPort $repository,
    ) {}

    #[\NoDiscard("UUID of the created zone must be captured")]
    public function handle(StoreZoneData $data): string
    {
        $id   = ZoneId::generate();
        $zone = Zone::create(
            id: $id,
            zoneName: $data->zoneName,
            zoneType: $data->zoneType,
            code: $data->code,
            description: $data->description,
            userId: $data->userId,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($zone);

        return $id->toString();
    }
}
