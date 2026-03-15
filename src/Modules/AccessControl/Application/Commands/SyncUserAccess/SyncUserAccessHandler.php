<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\Commands\SyncUserAccess;

use Modules\AccessControl\Application\DTOs\SyncUserAccessDTO;
use Modules\AccessControl\Application\Support\PrivilegedAccess;
use Modules\AccessControl\Domain\Ports\AccessControlAuditPort;
use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SyncUserAccessHandler
{
    public function __construct(
        private AccessControlRepositoryPort $repository,
        private AccessControlAuditPort $audit,
    ) {
    }

    #[\NoDiscard('Updated user access payload must be consumed')]
    public function handle(string $uuid, SyncUserAccessDTO $dto, bool $actorIsSuperAdmin): array
    {
        $userAccess = $this->repository->getUserAccess($uuid);

        if ($userAccess === null) {
            throw new NotFoundHttpException('User access not found.');
        }

        $roles = PrivilegedAccess::sanitizeRoles(
            requestedRoles: array_values($dto->roles),
            currentRoles: array_values($userAccess['roles'] ?? []),
            actorIsSuperAdmin: $actorIsSuperAdmin,
        );

        $permissions = PrivilegedAccess::sanitizePermissions(
            requestedPermissions: array_values($dto->permissions),
            currentPermissions: array_values($userAccess['direct_permissions'] ?? []),
            actorIsSuperAdmin: $actorIsSuperAdmin,
        );

        $updatedUserAccess = $this->repository->syncUserAccess($uuid, $roles, $permissions);

        $this->audit->log(
            logName: 'access_control.users',
            description: 'access_control.user.access_synced',
            properties: [
                'uuid' => $uuid,
                'roles' => $updatedUserAccess['roles'] ?? [],
                'direct_permissions' => $updatedUserAccess['direct_permissions'] ?? [],
            ],
        );

        return $updatedUserAccess;
    }
}
