<?php

declare(strict_types=1);

namespace Modules\AccessControl\Infrastructure\Persistence\Mappers;

use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AccessControlMapper
{
    public function mapPermission(Permission|PermissionEloquentModel $permission): array
    {
        return [
            'id' => $permission->id,
            'uuid' => $permission->uuid,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
            'roles_count' => (int) ($permission->roles_count ?? 0),
            'created_at' => $permission->created_at?->toIso8601String(),
            'updated_at' => $permission->updated_at?->toIso8601String(),
        ];
    }

    public function mapRoleSummary(Role|RoleEloquentModel $role): array
    {
        return [
            'id' => $role->id,
            'uuid' => $role->uuid,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions_count' => (int) ($role->permissions_count ?? 0),
            'permission_names' => $role->permissions->pluck('name')->values()->all(),
            'deleted_at' => $role->getAttribute('deleted_at')?->toIso8601String(),
            'created_at' => $role->created_at?->toIso8601String(),
            'updated_at' => $role->updated_at?->toIso8601String(),
        ];
    }

    public function mapRoleAccess(Role|RoleEloquentModel $role): array
    {
        return [
            'uuid' => $role->uuid,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permission_names' => $role->permissions->pluck('name')->values()->all(),
        ];
    }

    public function mapUserSearch(UserEloquentModel $user): array
    {
        return [
            'uuid' => $user->uuid,
            'name' => $this->fullName($user),
            'email' => $user->email,
        ];
    }

    public function mapUserAccess(UserEloquentModel $user): array
    {
        return [
            'uuid' => $user->uuid,
            'name' => $this->fullName($user),
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->values()->all(),
            'direct_permissions' => $user->permissions->pluck('name')->values()->all(),
            'effective_permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
        ];
    }

    private function fullName(UserEloquentModel $user): string
    {
        return [$user->name, $user->last_name ?? '']
            |> self::trimParts(...)
            |> self::removeEmptyParts(...)
            |> self::joinParts(...);
    }

    private static function trimParts(array $parts): array
    {
        return array_map(
            static fn (string $value): string => trim($value),
            $parts,
        );
    }

    private static function removeEmptyParts(array $parts): array
    {
        return array_values(array_filter($parts));
    }

    private static function joinParts(array $parts): string
    {
        return implode(' ', $parts);
    }
}
