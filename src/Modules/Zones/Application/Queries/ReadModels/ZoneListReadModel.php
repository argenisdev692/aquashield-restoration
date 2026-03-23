<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Application\Queries\ReadModels;

final class ZoneListReadModel
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $zoneName,
        public readonly string $zoneType,
        public readonly ?string $code,
        public readonly ?string $description,
        public readonly int $userId,
        public readonly string $createdAt,
        public readonly ?string $deletedAt,
    ) {}
}
