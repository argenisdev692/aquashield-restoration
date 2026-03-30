<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\Claims\Application\DTOs\ClaimFilterData;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;

final class ClaimExportQuery
{
    public static function build(ClaimFilterData $filters): Builder
    {
        return ClaimEloquentModel::query()
            ->with(['property', 'claimStatus', 'typeDamage', 'referredByUser'])
            ->when($filters->status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when(
                $filters->status !== null && $filters->status !== 'deleted',
                fn ($q) => $q->whereNull('deleted_at'),
            )
            ->search($filters->search)
            ->inDateRange($filters->dateFrom, $filters->dateTo)
            ->when($filters->claimStatusId, fn ($q, $id) => $q->where('claim_status', $id))
            ->when($filters->typeDamageId, fn ($q, $id) => $q->where('type_damage_id', $id))
            ->latest('created_at');
    }
}
