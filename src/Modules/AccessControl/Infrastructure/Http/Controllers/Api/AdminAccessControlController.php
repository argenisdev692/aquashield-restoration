<?php

declare(strict_types=1);

namespace Modules\AccessControl\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\AccessControl\Application\Commands\CreatePermission\CreatePermissionHandler;
use Modules\AccessControl\Application\Commands\SyncRolePermissions\SyncRolePermissionsHandler;
use Modules\AccessControl\Application\Commands\SyncUserAccess\SyncUserAccessHandler;
use Modules\AccessControl\Application\DTOs\CreatePermissionDTO;
use Modules\AccessControl\Application\DTOs\PermissionSearchDTO;
use Modules\AccessControl\Application\DTOs\SyncRolePermissionsDTO;
use Modules\AccessControl\Application\DTOs\SyncUserAccessDTO;
use Modules\AccessControl\Application\DTOs\UserAccessSearchDTO;
use Modules\AccessControl\Application\Queries\GetRoleAccess\GetRoleAccessHandler;
use Modules\AccessControl\Application\Queries\GetUserAccess\GetUserAccessHandler;
use Modules\AccessControl\Application\Queries\ListPermissions\ListPermissionsHandler;
use Modules\AccessControl\Application\Queries\ListRoles\ListRolesHandler;
use Modules\AccessControl\Application\Queries\SearchUsers\SearchUsersHandler;
use Modules\AccessControl\Infrastructure\Http\Requests\CreatePermissionRequest;
use Modules\AccessControl\Infrastructure\Http\Requests\PermissionSearchRequest;
use Modules\AccessControl\Infrastructure\Http\Requests\SyncRolePermissionsRequest;
use Modules\AccessControl\Infrastructure\Http\Requests\SyncUserAccessRequest;
use Modules\AccessControl\Infrastructure\Http\Requests\UserAccessSearchRequest;

final readonly class AdminAccessControlController
{
    public function __construct(
        private ListPermissionsHandler $listPermissions,
        private CreatePermissionHandler $createPermission,
        private ListRolesHandler $listRoles,
        private GetRoleAccessHandler $getRoleAccess,
        private SyncRolePermissionsHandler $syncRolePermissions,
        private SearchUsersHandler $searchUsers,
        private GetUserAccessHandler $getUserAccess,
        private SyncUserAccessHandler $syncUserAccess,
    ) {
    }

    public function permissions(PermissionSearchRequest $request): JsonResponse
    {
        $filters = PermissionSearchDTO::from($request->validated());

        return response()->json([
            'data' => $this->listPermissions->handle($filters),
        ]);
    }

    public function createPermission(CreatePermissionRequest $request): JsonResponse
    {
        $dto = CreatePermissionDTO::from($request->validated());

        return response()->json([
            'data' => $this->createPermission->handle($dto),
        ], 201);
    }

    public function roles(): JsonResponse
    {
        return response()->json([
            'data' => $this->listRoles->handle(),
        ]);
    }

    public function role(string $uuid): JsonResponse
    {
        $role = $this->getRoleAccess->handle($uuid);

        return response()->json([
            'data' => $role,
        ]);
    }

    public function syncRolePermissions(SyncRolePermissionsRequest $request, string $uuid): JsonResponse
    {
        $actorIsSuperAdmin = (bool) $request->user()?->hasRole('SUPER_ADMIN');
        $dto = SyncRolePermissionsDTO::from($request->validated());

        return response()->json([
            'data' => $this->syncRolePermissions->handle($uuid, $dto, $actorIsSuperAdmin),
        ]);
    }

    public function users(UserAccessSearchRequest $request): JsonResponse
    {
        $filters = UserAccessSearchDTO::from($request->validated());

        return response()->json([
            'data' => $this->searchUsers->handle($filters),
        ]);
    }

    public function user(string $uuid): JsonResponse
    {
        $user = $this->getUserAccess->handle($uuid);

        return response()->json([
            'data' => $user,
        ]);
    }

    public function syncUserAccess(SyncUserAccessRequest $request, string $uuid): JsonResponse
    {
        $actorIsSuperAdmin = (bool) $request->user()?->hasRole('SUPER_ADMIN');
        $dto = SyncUserAccessDTO::from($request->validated());

        return response()->json([
            'data' => $this->syncUserAccess->handle($uuid, $dto, $actorIsSuperAdmin),
        ]);
    }
}
