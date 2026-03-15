<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\Commands\SyncRolePermissions;

use Modules\AccessControl\Application\DTOs\SyncRolePermissionsDTO;
use Modules\AccessControl\Application\Support\PrivilegedAccess;
use Modules\AccessControl\Domain\Ports\AccessControlAuditPort;
use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SyncRolePermissionsHandler
{
    public function __construct(
        private AccessControlRepositoryPort $repository,
        private AccessControlAuditPort $audit,
    ) {
    }

    #[\NoDiscard('Updated role access payload must be consumed')]
    public function handle(string $uuid, SyncRolePermissionsDTO $dto, bool $actorIsSuperAdmin): array
    {
        $role = $this->repository->getRoleAccess($uuid);

        if ($role === null) {
            throw new NotFoundHttpException('Role access not found.');
        }

        if (($role['name'] ?? null) === 'SUPER_ADMIN' && ! $actorIsSuperAdmin) {
            throw new AccessDeniedHttpException('Only SUPER_ADMIN can update SUPER_ADMIN role access.');
        }

        $permissions = PrivilegedAccess::sanitizePermissions(
            requestedPermissions: array_values($dto->permissions),
            currentPermissions: array_values($role['permission_names'] ?? []),
            actorIsSuperAdmin: $actorIsSuperAdmin,
        );

        $updatedRole = $this->repository->syncRolePermissions($uuid, $permissions);

        $this->audit->log(
            logName: 'access_control.roles',
            description: 'access_control.role.permissions_synced',
            properties: [
                'uuid' => $uuid,
                'role' => $updatedRole['name'] ?? $role['name'] ?? null,
                'permission_names' => $updatedRole['permission_names'] ?? [],
            ],
        );

        return $updatedRole;
    }
}
