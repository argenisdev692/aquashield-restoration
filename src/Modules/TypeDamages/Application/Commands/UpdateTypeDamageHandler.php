<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Application\Commands;

use RuntimeException;
use Src\Modules\TypeDamages\Application\DTOs\UpdateTypeDamageData;
use Src\Modules\TypeDamages\Domain\Ports\TypeDamageRepositoryPort;
use Src\Modules\TypeDamages\Domain\ValueObjects\TypeDamageId;

final class UpdateTypeDamageHandler
{
    public function __construct(
        private readonly TypeDamageRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateTypeDamageData $data): void
    {
        $typeDamageId = TypeDamageId::fromString($uuid);
        $typeDamage = $this->repository->find($typeDamageId);

        if ($typeDamage === null) {
            throw new RuntimeException('Type damage not found.');
        }

        $typeDamage->update(
            typeDamageName: $data->typeDamageName,
            description: $data->description,
            severity: $data->severity,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($typeDamage);
    }
}
