<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Application\Commands;

use Src\Modules\TypeDamages\Domain\Ports\TypeDamageRepositoryPort;
use Src\Modules\TypeDamages\Domain\ValueObjects\TypeDamageId;

final class RestoreTypeDamageHandler
{
    public function __construct(
        private readonly TypeDamageRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(TypeDamageId::fromString($uuid));
    }
}
