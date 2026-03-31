<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Persistence\Mappers;

use Src\Modules\ScopeSheets\Domain\Entities\ScopeSheet;
use Src\Modules\ScopeSheets\Domain\ValueObjects\ScopeSheetId;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel;

final class ScopeSheetMapper
{
    public function toDomain(ScopeSheetEloquentModel $model): ScopeSheet
    {
        return ScopeSheet::reconstitute(
            id: ScopeSheetId::fromString($model->uuid),
            claimId: (int) $model->claim_id,
            generatedBy: (int) $model->generated_by,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
            scopeSheetDescription: $model->scope_sheet_description,
        );
    }

    public function toEloquent(ScopeSheet $scopeSheet): ScopeSheetEloquentModel
    {
        $model = ScopeSheetEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $scopeSheet->id()->toString(),
        ]);

        $model->uuid                     = $scopeSheet->id()->toString();
        $model->claim_id                 = $scopeSheet->claimId();
        $model->generated_by             = $scopeSheet->generatedBy();
        $model->scope_sheet_description  = $scopeSheet->scopeSheetDescription();

        return $model;
    }
}
