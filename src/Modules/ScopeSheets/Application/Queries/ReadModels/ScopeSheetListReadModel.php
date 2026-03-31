<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\Queries\ReadModels;

final class ScopeSheetListReadModel
{
    public function __construct(
        public string $uuid,
        public int $claimId,
        public ?string $claimNumber,
        public ?string $claimInternalId,
        public int $generatedBy,
        public ?string $generatedByName,
        public ?string $scopeSheetDescription,
        public int $presentationsCount,
        public int $zonesCount,
        public string $status,
        public string $createdAt,
        public ?string $deletedAt,
    ) {}
}
