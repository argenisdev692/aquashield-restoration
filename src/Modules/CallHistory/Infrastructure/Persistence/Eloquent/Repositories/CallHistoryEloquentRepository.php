<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Modules\CallHistory\Domain\Entities\CallHistory;
use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;
use Modules\CallHistory\Domain\ValueObjects\CallHistoryId;
use Modules\CallHistory\Infrastructure\Persistence\Eloquent\Mappers\CallHistoryMapper;
use Modules\CallHistory\Infrastructure\Persistence\Eloquent\Models\CallHistoryEloquentModel;

final readonly class CallHistoryEloquentRepository implements CallHistoryRepositoryPort
{
    public function __construct(
        private CallHistoryMapper $mapper
    ) {
    }

    public function findByUuid(CallHistoryId $uuid): ?CallHistory
    {
        $model = CallHistoryEloquentModel::where('uuid', $uuid->value())->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByCallId(string $callId): ?CallHistory
    {
        $model = CallHistoryEloquentModel::where('call_id', $callId)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByCallIdWithTrashed(string $callId): ?CallHistory
    {
        $model = CallHistoryEloquentModel::withTrashed()->where('call_id', $callId)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function listPaginated(
        ?string $search = null,
        ?string $status = null,
        ?string $direction = null,
        ?string $callType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        string $sortField = 'start_timestamp',
        string $sortDirection = 'desc',
        int $perPage = 10,
        int $page = 1
    ): array {
        $query = CallHistoryEloquentModel::query();

        $query = $this->applyFilters($query, $search, $status, $direction, $callType, $dateFrom, $dateTo);

        $query->orderBy($sortField, $sortDirection);

        Paginator::currentPageResolver(static fn () => $page);

        $paginated = $query->paginate($perPage);

        return array_map(
            fn ($model) => $this->mapper->toDomain($model),
            $paginated->items()
        );
    }

    public function count(
        ?string $search = null,
        ?string $status = null,
        ?string $direction = null,
        ?string $callType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        $query = CallHistoryEloquentModel::query();

        $query = $this->applyFilters($query, $search, $status, $direction, $callType, $dateFrom, $dateTo);

        return $query->count();
    }

    private function applyFilters(
        Builder $query,
        ?string $search,
        ?string $status,
        ?string $direction,
        ?string $callType,
        ?string $dateFrom,
        ?string $dateTo
    ): Builder {
        if ($search !== null && $search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('from_number', 'like', "%{$search}%")
                    ->orWhere('to_number', 'like', "%{$search}%")
                    ->orWhere('transcript', 'like', "%{$search}%")
                    ->orWhere('disconnection_reason', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('call_status', $status);
        }

        if ($direction !== null && $direction !== '') {
            $query->where('direction', $direction);
        }

        if ($callType !== null && $callType !== '') {
            $query->where('call_type', $callType);
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $query->whereDate('start_timestamp', '>=', $dateFrom);
        }

        if ($dateTo !== null && $dateTo !== '') {
            $query->whereDate('start_timestamp', '<=', $dateTo);
        }

        return $query;
    }

    public function save(CallHistory $callHistory): void
    {
        $model = $this->mapper->toEloquent($callHistory);
        $model->save();
    }

    public function update(CallHistory $callHistory): void
    {
        $model = CallHistoryEloquentModel::where('uuid', $callHistory->uuid()->value())->first();

        if ($model === null) {
            throw new \DomainException("Call history with UUID {$callHistory->uuid()->value()} not found");
        }

        $this->mapper->updateEloquent($callHistory, $model);
        $model->save();
    }

    public function delete(CallHistoryId $uuid): void
    {
        $model = CallHistoryEloquentModel::where('uuid', $uuid->value())->first();

        if ($model === null) {
            throw new \DomainException("Call history with UUID {$uuid->value()} not found");
        }

        $model->delete();
    }

    public function restore(CallHistoryId $uuid): void
    {
        $model = CallHistoryEloquentModel::withTrashed()->where('uuid', $uuid->value())->first();

        if ($model === null) {
            throw new \DomainException("Call history with UUID {$uuid->value()} not found");
        }

        $model->restore();
    }

    public function bulkDelete(array $uuids): int
    {
        return CallHistoryEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
