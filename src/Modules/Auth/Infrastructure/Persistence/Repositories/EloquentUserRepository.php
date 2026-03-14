<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Repositories;

use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\Ports\UserRepositoryPort;
use Modules\Auth\Domain\ValueObjects\UserEmail;
use Modules\Auth\Infrastructure\Persistence\Mappers\UserMapper;

/**
 * EloquentUserRepository — Eloquent adapter implementing UserRepositoryPort.
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
        'profile_photo_path',
        'phone',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function findByEmail(UserEmail $email): ?User
    {
        $eloquentUser = UserEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->where('email', $email->value)
            ->first();

        return $eloquentUser ? UserMapper::toDomain($eloquentUser) : null;
    }

    public function findByEmailOrPhone(string $identifier): ?User
    {
        $eloquentUser = UserEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        return $eloquentUser ? UserMapper::toDomain($eloquentUser) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $eloquentUser = UserEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->where('username', $username)
            ->first();

        return $eloquentUser ? UserMapper::toDomain($eloquentUser) : null;
    }

    public function findById(int $id): ?User
    {
        $eloquentUser = UserEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->whereKey($id)
            ->first();

        return $eloquentUser ? UserMapper::toDomain($eloquentUser) : null;
    }

    public function findByUuid(string $uuid): ?User
    {
        $eloquentUser = UserEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->where('uuid', $uuid)
            ->first();

        return $eloquentUser ? UserMapper::toDomain($eloquentUser) : null;
    }

    public function getPasswordHashById(int $id): ?string
    {
        /** @var string|null $password */
        $password = UserEloquentModel::query()
            ->whereKey($id)
            ->value('password');

        return $password;
    }

    public function create(array $data): User
    {
        $eloquentUser = UserEloquentModel::create($data);
        return UserMapper::toDomain($eloquentUser);
    }

    public function update(User $user, array $data): User
    {
        $eloquentUser = UserEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->whereKey($user->id)
            ->first();

        if ($eloquentUser) {
            $eloquentUser->update($data);
            return UserMapper::toDomain($eloquentUser->fresh() ?? $eloquentUser);
        }

        return $user;
    }

    public function paginate(
        int $page,
        int $perPage,
        ?string $search,
        ?bool $emailVerified,
        ?string $sortBy,
        string $sortDirection,
    ): array {
        $resolvedSortBy = $this->resolveSortBy($sortBy);
        $resolvedSortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $paginator = UserEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($emailVerified !== null, function ($query) use ($emailVerified): void {
                if ($emailVerified) {
                    $query->whereNotNull('email_verified_at');

                    return;
                }

                $query->whereNull('email_verified_at');
            })
            ->orderBy($resolvedSortBy, $resolvedSortDirection)
            ->paginate($perPage, self::SELECT_COLUMNS, 'page', $page);

        return [
            'data' => array_map(
                static fn(UserEloquentModel $user): User => UserMapper::toDomain($user),
                $paginator->items(),
            ),
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    private function resolveSortBy(?string $sortBy): string
    {
        return match ($sortBy) {
            'name', 'email', 'username', 'created_at', 'updated_at' => $sortBy,
            default => 'created_at',
        };
    }
}
