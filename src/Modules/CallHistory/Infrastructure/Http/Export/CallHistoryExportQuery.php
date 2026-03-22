<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Modules\CallHistory\Application\DTOs\CallHistoryFilterData;
use Modules\CallHistory\Infrastructure\Persistence\Eloquent\Models\CallHistoryEloquentModel;

final class CallHistoryExportQuery
{
    public static function build(CallHistoryFilterData $filters): Builder
    {
        return CallHistoryEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'call_id',
                'agent_id',
                'agent_name',
                'from_number',
                'to_number',
                'direction',
                'call_status',
                'call_type',
                'start_timestamp',
                'end_timestamp',
                'duration_ms',
                'disconnection_reason',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function (Builder $query, string $search): Builder {
                return $query->where(function (Builder $q) use ($search): void {
                    $q->where('call_id', 'like', "%{$search}%")
                        ->orWhere('agent_name', 'like', "%{$search}%")
                        ->orWhere('from_number', 'like', "%{$search}%")
                        ->orWhere('to_number', 'like', "%{$search}%");
                });
            })
            ->when($filters->status === 'active', static fn (Builder $query): Builder => $query->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $query): Builder => $query->onlyTrashed())
            ->when($filters->direction, static fn (Builder $query, string $direction): Builder => $query->where('direction', $direction))
            ->when($filters->callType, static fn (Builder $query, string $callType): Builder => $query->where('call_type', $callType))
            ->when($filters->dateFrom, static fn (Builder $query, string $dateFrom): Builder => $query->whereDate('start_timestamp', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $query, string $dateTo): Builder => $query->whereDate('start_timestamp', '<=', $dateTo))
            ->orderByDesc('start_timestamp');
    }
}
