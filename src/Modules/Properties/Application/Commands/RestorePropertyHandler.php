<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Application\Commands;

use Src\Modules\Properties\Domain\Ports\PropertyRepositoryPort;
use Src\Modules\Properties\Domain\ValueObjects\PropertyId;

final class RestorePropertyHandler
{
    public function __construct(
        private readonly PropertyRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(PropertyId::fromString($uuid));
    }
}
