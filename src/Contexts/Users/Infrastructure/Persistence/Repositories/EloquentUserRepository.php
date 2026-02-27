<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Infrastructure\Persistence\Repositories;

use Src\Contexts\Users\Domain\Entities\User;
use Src\Contexts\Users\Domain\Ports\UserRepositoryPort;
use Src\Contexts\Users\Infrastructure\Persistence\Mappers\UserMapper;
use Src\Contexts\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/**
 * EloquentUserRepository — Implements UserRepositoryPort using Eloquent.
 *
 * All public-facing lookups use the `uuid` column, never `id`.
 */
final class EloquentUserRepository implements UserRepositoryPort
{
    private const SELECT_COLUMNS = [
        'id',
        'uuid',
        'name',
        'last_name',
        'email',
        'username',
        'phone',
        'profile_photo_path',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function findByUuid(string $uuid): ?User
    {
        $model = UserEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->where('uuid', $uuid)
            ->first();

        return $model ? UserMapper::toDomain($model) : null;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{data: list<User>, total: int, perPage: int, currentPage: int, lastPage: int}
     */
    public function findAllPaginated(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $query = UserEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->whereNull('deleted_at')
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) => $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                }),
            )
            ->when(
                ($filters['dateFrom'] ?? null) || ($filters['dateTo'] ?? null),
                fn($q) => $q->inDateRange($filters['dateFrom'] ?? null, $filters['dateTo'] ?? null),
            )
            ->orderBy(
                $filters['sortBy'] ?? 'created_at',
                $filters['sortDir'] ?? 'desc',
            );

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        return [
            'data' => array_map(
                fn(UserEloquentModel $model) => UserMapper::toDomain($model),
                $paginator->items(),
            ),
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): User
    {
        $model = UserEloquentModel::query()->create($data);

        return UserMapper::toDomain($model);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $uuid, array $data): User
    {
        $model = UserEloquentModel::query()->where('uuid', $uuid)->firstOrFail();
        $model->update($data);
        $model->refresh();

        return UserMapper::toDomain($model);
    }

    public function softDelete(string $uuid): void
    {
        $model = UserEloquentModel::query()->where('uuid', $uuid)->firstOrFail();
        $model->delete();
    }

    public function restore(string $uuid): void
    {
        $model = UserEloquentModel::query()->withTrashed()->where('uuid', $uuid)->firstOrFail();
        $model->restore();
    }
}
