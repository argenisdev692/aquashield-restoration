<?php

declare(strict_types=1);

namespace Modules\Roles\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Roles\Domain\Ports\RoleRepositoryPort;
use Modules\Roles\Infrastructure\Http\Requests\CreateRoleRequest;
use Modules\Roles\Infrastructure\Http\Requests\RoleFilterRequest;
use Modules\Roles\Infrastructure\Http\Requests\UpdateRoleRequest;
use Modules\Roles\Infrastructure\Http\Resources\RoleResource;

final readonly class AdminRoleController
{
    public function __construct(
        private RoleRepositoryPort $roles,
    ) {
    }

    public function index(RoleFilterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->roles->paginate([
            'page' => $validated['page'] ?? 1,
            'perPage' => $validated['per_page'] ?? 15,
            'search' => $validated['search'] ?? null,
            'sortBy' => $validated['sort_by'] ?? 'name',
            'sortDir' => $validated['sort_dir'] ?? 'asc',
        ]);

        return response()->json([
            'data' => RoleResource::collection(collect($result['data']))->resolve($request),
            'meta' => [
                'currentPage' => $result['currentPage'],
                'lastPage' => $result['lastPage'],
                'perPage' => $result['perPage'],
                'total' => $result['total'],
            ],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $role = $this->roles->findByUuid($uuid);
        abort_if($role === null, 404);

        return response()->json([
            'data' => RoleResource::make($role)->resolve(request()),
        ]);
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $role = $this->roles->create($request->validated());

        return response()->json([
            'data' => RoleResource::make($role)->resolve($request),
        ], 201);
    }

    public function update(UpdateRoleRequest $request, string $uuid): JsonResponse
    {
        $existingRole = $this->roles->findByUuid($uuid);
        abort_if($existingRole === null, 404);
        abort_if($existingRole['name'] === 'SUPER_ADMIN' && ! $request->user()?->hasRole('SUPER_ADMIN'), 403);

        $role = $this->roles->update($uuid, $request->validated());

        return response()->json([
            'data' => RoleResource::make($role)->resolve($request),
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $existingRole = $this->roles->findByUuid($uuid);
        abort_if($existingRole === null, 404);
        abort_if($existingRole['name'] === 'SUPER_ADMIN' && ! request()->user()?->hasRole('SUPER_ADMIN'), 403);

        $this->roles->delete($uuid);

        return response()->json(null, 204);
    }

    public function restore(string $uuid): JsonResponse
    {
        $existingRole = $this->roles->findByUuid($uuid);
        abort_if($existingRole === null, 404);
        abort_if($existingRole['name'] === 'SUPER_ADMIN' && ! request()->user()?->hasRole('SUPER_ADMIN'), 403);

        $this->roles->restore($uuid);

        return response()->json([
            'message' => 'Role restored successfully.',
        ]);
    }
}
