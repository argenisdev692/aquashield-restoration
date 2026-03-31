<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\Commands;

use Src\Modules\ScopeSheets\Domain\Ports\ScopeSheetRepositoryPort;

final class RestoreScopeSheetHandler
{
    public function __construct(
        private readonly ScopeSheetRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore($uuid);
    }
}
