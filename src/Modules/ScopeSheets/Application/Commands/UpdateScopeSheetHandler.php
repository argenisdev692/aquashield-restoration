<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\Commands;

use RuntimeException;
use Src\Modules\ScopeSheets\Application\DTOs\UpdateScopeSheetData;
use Src\Modules\ScopeSheets\Domain\Ports\ScopeSheetRepositoryPort;

final class UpdateScopeSheetHandler
{
    public function __construct(
        private readonly ScopeSheetRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateScopeSheetData $data): void
    {
        $scopeSheet = $this->repository->findByUuid($uuid);

        if ($scopeSheet === null) {
            throw new RuntimeException("ScopeSheet [{$uuid}] not found.");
        }

        $scopeSheet->update(
            scopeSheetDescription: $data->scopeSheetDescription,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($scopeSheet);

        $this->repository->syncRelations($uuid, $data->presentations, $data->zones);
    }
}
