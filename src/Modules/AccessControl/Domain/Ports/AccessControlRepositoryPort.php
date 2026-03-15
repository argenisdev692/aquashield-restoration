<?php

declare(strict_types=1);

namespace Modules\AccessControl\Domain\Ports;

interface AccessControlRepositoryPort
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listPermissions(?string $search = null): array;

    /**
     * @return array<string, mixed>
     */
    public function createPermission(string $name): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listRoles(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function getRoleAccess(string $uuid): ?array;

    /**
     * @param list<string> $permissions
     * @return array<string, mixed>
     */
    public function syncRolePermissions(string $uuid, array $permissions): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function searchUsers(?string $search, int $limit = 10): array;

    /**
     * @return array<string, mixed>|null
     */
    public function getUserAccess(string $uuid): ?array;

    /**
     * @param list<string> $roles
     * @param list<string> $permissions
     * @return array<string, mixed>
     */
    public function syncUserAccess(string $uuid, array $roles, array $permissions): array;
}
