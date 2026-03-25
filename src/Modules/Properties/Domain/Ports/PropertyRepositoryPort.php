<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Domain\Ports;

use Src\Modules\Properties\Domain\Entities\Property;
use Src\Modules\Properties\Domain\ValueObjects\PropertyId;

interface PropertyRepositoryPort
{
    public function find(PropertyId $id): ?Property;

    public function save(Property $property): void;

    public function softDelete(PropertyId $id): void;

    public function restore(PropertyId $id): void;

    /**
     * @param array<int, PropertyId> $ids
     */
    public function bulkSoftDelete(array $ids): int;
}
