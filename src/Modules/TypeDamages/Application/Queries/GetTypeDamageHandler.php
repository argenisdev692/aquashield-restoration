<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Application\Queries;

use Src\Modules\TypeDamages\Application\Queries\ReadModels\TypeDamageReadModel;
use Src\Modules\TypeDamages\Domain\Ports\TypeDamageRepositoryPort;
use Src\Modules\TypeDamages\Domain\ValueObjects\TypeDamageId;

final class GetTypeDamageHandler
{
    public function __construct(
        private readonly TypeDamageRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): ?TypeDamageReadModel
    {
        $typeDamage = $this->repository->find(TypeDamageId::fromString($uuid));

        if ($typeDamage === null) {
            return null;
        }

        return new TypeDamageReadModel(
            uuid: $typeDamage->id()->toString(),
            typeDamageName: $typeDamage->typeDamageName(),
            description: $typeDamage->description(),
            severity: $typeDamage->severity(),
            createdAt: $typeDamage->createdAt(),
            updatedAt: $typeDamage->updatedAt(),
            deletedAt: $typeDamage->deletedAt(),
        );
    }
}
