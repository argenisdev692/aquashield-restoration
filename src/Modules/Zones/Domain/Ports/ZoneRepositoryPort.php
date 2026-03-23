<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Domain\Ports;

use Src\Modules\Zones\Domain\Entities\Zone;
use Src\Modules\Zones\Domain\ValueObjects\ZoneId;

interface ZoneRepositoryPort
{
    public function find(ZoneId $id): ?Zone;

    public function save(Zone $zone): void;

    public function softDelete(ZoneId $id): void;

    public function restore(ZoneId $id): void;

    public function bulkSoftDelete(array $ids): int;
}
