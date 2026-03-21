<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Application\Commands;

use Src\Modules\ProjectTypes\Domain\Ports\ProjectTypeRepositoryPort;
use Src\Modules\ProjectTypes\Domain\ValueObjects\ProjectTypeId;

final class RestoreProjectTypeHandler
{
    public function __construct(
        private readonly ProjectTypeRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(ProjectTypeId::fromString($uuid));
    }
}
