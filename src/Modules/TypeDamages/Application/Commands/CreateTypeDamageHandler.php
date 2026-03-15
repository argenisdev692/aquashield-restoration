<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Application\Commands;

use Src\Modules\TypeDamages\Application\DTOs\StoreTypeDamageData;
use Src\Modules\TypeDamages\Domain\Entities\TypeDamage;
use Src\Modules\TypeDamages\Domain\Ports\TypeDamageRepositoryPort;
use Src\Modules\TypeDamages\Domain\ValueObjects\TypeDamageId;

final class CreateTypeDamageHandler
{
    public function __construct(
        private readonly TypeDamageRepositoryPort $repository,
    ) {}

    public function handle(StoreTypeDamageData $data): string
    {
        $id = TypeDamageId::generate();
        $typeDamage = TypeDamage::create(
            id: $id,
            typeDamageName: $data->typeDamageName,
            description: $data->description,
            severity: $data->severity,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($typeDamage);

        return $id->toString();
    }
}
