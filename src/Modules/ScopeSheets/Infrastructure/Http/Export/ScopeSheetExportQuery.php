<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\ScopeSheets\Application\DTOs\ScopeSheetFilterData;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel;

final class ScopeSheetExportQuery
{
    public static function build(ScopeSheetFilterData $filters): Builder
    {
        return ScopeSheetEloquentModel::query()
            ->with([
                'claim:id,uuid,claim_number,claim_internal_id',
                'generatedByUser:id,uuid,name',
            ])
            ->withCount(['presentations', 'zones'])
            ->when($filters->status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when(
                $filters->status !== null && $filters->status !== 'deleted',
                fn ($q) => $q->whereNull('deleted_at'),
            )
            ->search($filters->search)
            ->inDateRange($filters->dateFrom, $filters->dateTo)
            ->when($filters->claimId, fn ($q, $id) => $q->where('claim_id', $id))
            ->orderBy('created_at', 'desc');
    }
}
