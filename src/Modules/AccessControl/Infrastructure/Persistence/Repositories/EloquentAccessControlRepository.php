<?php

declare(strict_types=1);

namespace Modules\AccessControl\Infrastructure\Persistence\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;
use Modules\AccessControl\Infrastructure\Persistence\Mappers\AccessControlMapper;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

final class EloquentAccessControlRepository implements AccessControlRepositoryPort
{
    private const DEFAULT_GUARD = 'web';

    public function __construct(
        private readonly AccessControlMapper $mapper,
    ) {
    }

    public function listPermissions(?string $search = null): array
    {
        $query = $this->newPermissionQuery()
            ->withCount('roles')
            ->where('guard_name', self::DEFAULT_GUARD)
            ->when(
                $search,
                static fn ($builder, $value) => $builder->where('name', 'like', '%' . trim((string) $value) . '%'),
            )
            ->orderBy('name');

        return $query
            ->get(['id', 'uuid', 'name', 'guard_name', 'created_at', 'updated_at'])
            ->map(fn ($permission): array => $this->mapper->mapPermission($permission))
            ->all();
    }

    public function createPermission(string $name): array
    {
        $permission = $this->newPermissionQuery()->firstOrCreate([
            'name' => $name,
            'guard_name' => self::DEFAULT_GUARD,
        ]);

        $permission->loadCount('roles');

        return $this->mapper->mapPermission($permission);
    }

    public function listRoles(): array
    {
        return $this->newRoleQuery()
            ->where('guard_name', self::DEFAULT_GUARD)
            ->withCount('permissions')
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get(['id', 'uuid', 'name', 'guard_name', 'created_at', 'updated_at'])
            ->map(fn ($role): array => $this->mapper->mapRoleSummary($role))
            ->all();
    }

    public function getRoleAccess(string $uuid): ?array
    {
        $role = $this->newRoleQuery()
            ->with('permissions:id,name')
            ->where('guard_name', self::DEFAULT_GUARD)
            ->where('uuid', $uuid)
            ->first();

        if ($role === null) {
            return null;
        }

        return $this->mapper->mapRoleAccess($role);
    }

    public function syncRolePermissions(string $uuid, array $permissions): array
    {
        $role = $this->newRoleQuery()
            ->where('guard_name', self::DEFAULT_GUARD)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $role->syncPermissions($permissions);
        $role->loadCount('permissions')->load('permissions:id,name');

        return $this->mapper->mapRoleAccess($role) + [
            'permissions_count' => (int) ($role->permissions_count ?? 0),
        ];
    }

    public function searchUsers(?string $search, int $limit = 10): array
    {
        return UserEloquentModel::query()
            ->select(['id', 'uuid', 'name', 'last_name', 'email'])
            ->when(
                is_string($search) && $search !== '',
                static function ($builder) use ($search) {
                    $builder->where(function ($query) use ($search): void {
                        $query->where('name', 'like', '%' . trim($search) . '%')
                            ->orWhere('last_name', 'like', '%' . trim($search) . '%')
                            ->orWhere('email', 'like', '%' . trim($search) . '%');
                    });
                },
            )
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (UserEloquentModel $user): array => $this->mapper->mapUserSearch($user))
            ->all();
    }

    public function getUserAccess(string $uuid): ?array
    {
        $user = UserEloquentModel::query()
            ->with(['roles:id,name', 'permissions:id,name'])
            ->where('uuid', $uuid)
            ->first();

        if ($user === null) {
            return null;
        }

        return $this->mapper->mapUserAccess($user);
    }

    public function syncUserAccess(string $uuid, array $roles, array $permissions): array
    {
        $user = UserEloquentModel::query()
            ->with(['roles:id,name', 'permissions:id,name'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $user->syncRoles($roles);
        $user->syncPermissions($permissions);
        $user->load(['roles:id,name', 'permissions:id,name']);

        return $this->mapper->mapUserAccess($user);
    }

    private function newRoleQuery(): Builder
    {
        $roleModelClass = (string) config('permission.models.role');

        return $roleModelClass::query();
    }

    private function newPermissionQuery(): Builder
    {
        $permissionModelClass = (string) config('permission.models.permission');

        return $permissionModelClass::query();
    }
}
