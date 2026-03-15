<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Domain\Ports;

use Src\Modules\TypeDamages\Domain\Entities\TypeDamage;
use Src\Modules\TypeDamages\Domain\ValueObjects\TypeDamageId;

interface TypeDamageRepositoryPort
{
    public function find(TypeDamageId $id): ?TypeDamage;

    public function save(TypeDamage $typeDamage): void;

    public function softDelete(TypeDamageId $id): void;

    public function restore(TypeDamageId $id): void;

    public function bulkSoftDelete(array $ids): int;
}
