<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\Commands;

use Src\Modules\ScopeSheets\Application\DTOs\StoreScopeSheetData;
use Src\Modules\ScopeSheets\Domain\Entities\ScopeSheet;
use Src\Modules\ScopeSheets\Domain\Ports\ScopeSheetRepositoryPort;
use Src\Modules\ScopeSheets\Domain\ValueObjects\ScopeSheetId;

final class CreateScopeSheetHandler
{
    public function __construct(
        private readonly ScopeSheetRepositoryPort $repository,
    ) {}

    #[\NoDiscard('UUID of the created scope sheet must be captured')]
    public function handle(StoreScopeSheetData $data): string
    {
        $scopeSheet = ScopeSheet::create(
            id: ScopeSheetId::generate(),
            claimId: $data->claimId,
            generatedBy: $data->generatedBy,
            createdAt: now()->toIso8601String(),
            scopeSheetDescription: $data->scopeSheetDescription,
        );

        $this->repository->save($scopeSheet);

        $uuid = $scopeSheet->id()->toString();

        $this->repository->syncRelations($uuid, $data->presentations, $data->zones);

        return $uuid;
    }
}
