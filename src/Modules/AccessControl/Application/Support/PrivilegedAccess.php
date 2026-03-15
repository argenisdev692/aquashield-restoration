<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\Support;

final class PrivilegedAccess
{
    private const SUPER_ADMIN_ROLE = 'SUPER_ADMIN';

    /**
     * @var list<string>
     */
    private const PROTECTED_PERMISSIONS = [
        'CREATE_ROLE',
        'READ_ROLE',
        'UPDATE_ROLE',
        'DELETE_ROLE',
        'RESTORE_ROLE',
        'CREATE_PERMISSION',
        'READ_PERMISSION',
        'UPDATE_PERMISSION',
        'DELETE_PERMISSION',
        'RESTORE_PERMISSION',
    ];

    /**
     * @param list<string> $requestedRoles
     * @param list<string> $currentRoles
     * @return list<string>
     */
    #[\NoDiscard('Sanitized roles must be consumed')]
    public static function sanitizeRoles(array $requestedRoles, array $currentRoles, bool $actorIsSuperAdmin): array
    {
        $roles = self::normalizeList($requestedRoles);

        if ($actorIsSuperAdmin) {
            return $roles;
        }

        $roles = array_values(array_filter(
            $roles,
            static fn (string $role): bool => $role !== self::SUPER_ADMIN_ROLE,
        ));

        if (in_array(self::SUPER_ADMIN_ROLE, $currentRoles, true)) {
            $roles[] = self::SUPER_ADMIN_ROLE;
        }

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $requestedPermissions
     * @param list<string> $currentPermissions
     * @return list<string>
     */
    #[\NoDiscard('Sanitized permissions must be consumed')]
    public static function sanitizePermissions(array $requestedPermissions, array $currentPermissions, bool $actorIsSuperAdmin): array
    {
        $permissions = self::normalizeList($requestedPermissions);

        if ($actorIsSuperAdmin) {
            return $permissions;
        }

        $permissions = array_values(array_filter(
            $permissions,
            static fn (string $permission): bool => ! in_array($permission, self::PROTECTED_PERMISSIONS, true),
        ));

        foreach ($currentPermissions as $permission) {
            if (in_array($permission, self::PROTECTED_PERMISSIONS, true)) {
                $permissions[] = $permission;
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * @param list<string> $values
     * @return list<string>
     */
    #[\NoDiscard('Normalized list must be consumed')]
    private static function normalizeList(array $values): array
    {
        return $values
            |> self::trimValues(...)
            |> self::removeEmptyValues(...)
            |> self::uniqueValues(...);
    }

    private static function trimValues(array $values): array
    {
        return array_map(
            static fn (mixed $value): string => trim((string) $value),
            $values,
        );
    }

    private static function removeEmptyValues(array $values): array
    {
        return array_values(array_filter($values));
    }

    private static function uniqueValues(array $values): array
    {
        return array_values(array_unique($values));
    }
}
