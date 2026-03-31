<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\Commands;

use Src\Modules\ScopeSheets\Application\DTOs\BulkDeleteScopeSheetData;
use Src\Modules\ScopeSheets\Domain\Ports\ScopeSheetRepositoryPort;

final class BulkDeleteScopeSheetHandler
{
    public function __construct(
        private readonly ScopeSheetRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteScopeSheetData $data): int
    {
        return $this->repository->bulkDelete($data->uuids);
    }
}
