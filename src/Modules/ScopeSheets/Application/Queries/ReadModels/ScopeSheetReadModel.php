<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\Queries\ReadModels;

final class ScopeSheetReadModel
{
    public function __construct(
        public string $uuid,
        public int $claimId,
        public ?string $claimNumber,
        public ?string $claimInternalId,
        public ?string $propertyAddress,
        public int $generatedBy,
        public ?string $generatedByName,
        public ?string $scopeSheetDescription,
        public array $presentations,
        public array $zones,
        public ?array $exportRecord,
        public string $status,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt,
    ) {}
}
