<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Users\Application\Commands\BulkDeleteUsers\BulkDeleteUsersCommand;
use Modules\Users\Application\Commands\BulkDeleteUsers\BulkDeleteUsersHandler;
use Modules\Users\Application\Commands\ActivateUser\ActivateUserCommand;
use Modules\Users\Application\Commands\ActivateUser\ActivateUserHandler;
use Modules\Users\Application\Commands\CreateUser\CreateUserCommand;
use Modules\Users\Application\Commands\CreateUser\CreateUserHandler;
use Modules\Users\Application\Commands\DeleteUser\DeleteUserCommand;
use Modules\Users\Application\Commands\DeleteUser\DeleteUserHandler;
use Modules\Users\Application\Commands\SuspendUser\SuspendUserCommand;
use Modules\Users\Application\Commands\SuspendUser\SuspendUserHandler;
use Modules\Users\Application\Commands\UpdateUser\UpdateUserCommand;
use Modules\Users\Application\Commands\UpdateUser\UpdateUserHandler;
use Modules\Users\Application\Commands\RestoreUser\RestoreUserCommand;
use Modules\Users\Application\Commands\RestoreUser\RestoreUserHandler;
use Modules\Users\Application\DTOs\CreateUserDTO;
use Modules\Users\Application\DTOs\UpdateUserDTO;
use Modules\Users\Application\DTOs\UserFilterDTO;
use Modules\Users\Application\Queries\GetUser\GetUserHandler;
use Modules\Users\Application\Queries\GetUser\GetUserQuery;
use Modules\Users\Application\Queries\ListUsers\ListUsersHandler;
use Modules\Users\Application\Queries\ListUsers\ListUsersQuery;
use Modules\Users\Infrastructure\Http\Requests\BulkDeleteUsersRequest;
use Modules\Users\Infrastructure\Http\Requests\CreateUserRequest;
use Modules\Users\Infrastructure\Http\Requests\UpdateUserRequest;
use Modules\Users\Infrastructure\Http\Requests\UserFilterRequest;
use Modules\Users\Infrastructure\Http\Resources\UserResource;

/**
 * AdminUserController — Full CRUD API for super-admin user management.
 *
 * @OA\Tag(name="Users", description="Users CRUD operations")
 */
final class AdminUserController
{
    public function __construct(
        private readonly BulkDeleteUsersHandler $bulkDeleteHandler,
        private readonly CreateUserHandler $createHandler,
        private readonly UpdateUserHandler $updateHandler,
        private readonly DeleteUserHandler $deleteHandler,
        private readonly SuspendUserHandler $suspendHandler,
        private readonly ActivateUserHandler $activateHandler,
        private readonly RestoreUserHandler $restoreHandler,
        private readonly ListUsersHandler $listHandler,
        private readonly GetUserHandler $getHandler,
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/users/admin",
     *     tags={"Users"},
     *     summary="List users",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Paginated users list"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(UserFilterRequest $request): JsonResponse
    {
        $filters = UserFilterDTO::from($request->validated());
        $result = $this->listHandler->handle(new ListUsersQuery($filters));

        return response()->json([
            'data' => $result['data'],
            'meta' => [
                'currentPage' => $result['currentPage'],
                'lastPage' => $result['lastPage'],
                'perPage' => $result['perPage'],
                'total' => $result['total'],
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/admin/{uuid}",
     *     tags={"Users"},
     *     summary="Show user",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="User detail", @OA\JsonContent(
     *         @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *     )),
     *     @OA\Response(response=404, description="User not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid): JsonResponse
    {
        $user = $this->getHandler->handle(new GetUserQuery($uuid));

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/admin",
     *     tags={"Users"},
     *     summary="Create user",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CreateUserDTO")),
     *     @OA\Response(response=201, description="User created", @OA\JsonContent(
     *         @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *     )),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $dto = CreateUserDTO::from($request->validated());
        $user = $this->createHandler->handle(new CreateUserCommand($dto));

        return response()->json([
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/users/admin/{uuid}",
     *     tags={"Users"},
     *     summary="Update user",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateUserDTO")),
     *     @OA\Response(response=200, description="User updated", @OA\JsonContent(
     *         @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *     )),
     *     @OA\Response(response=404, description="User not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(UpdateUserRequest $request, string $uuid): JsonResponse
    {
        $dto = UpdateUserDTO::from($request->validated());
        $user = $this->updateHandler->handle(new UpdateUserCommand($uuid, $dto));

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/admin/{uuid}",
     *     tags={"Users"},
     *     summary="Delete user",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=204, description="User deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteUserCommand($uuid));

        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/users/admin/bulk-delete",
     *     tags={"Users"},
     *     summary="Bulk soft-delete users",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"uuids"},
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(response=204, description="Users deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteUsersRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->bulkDeleteHandler->handle(new BulkDeleteUsersCommand($validated['uuids']));

        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/users/admin/{uuid}/suspend",
     *     tags={"Users"},
     *     summary="Suspend user",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="User suspended"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function suspend(string $uuid): JsonResponse
    {
        $this->suspendHandler->handle(new SuspendUserCommand($uuid));

        return response()->json(['message' => 'User suspended successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/users/admin/{uuid}/activate",
     *     tags={"Users"},
     *     summary="Activate user",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="User activated"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function activate(string $uuid): JsonResponse
    {
        $this->activateHandler->handle(new ActivateUserCommand($uuid));

        return response()->json(['message' => 'User activated successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/users/admin/{uuid}/restore",
     *     tags={"Users"},
     *     summary="Restore user",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="User restored"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid): JsonResponse
    {
        $this->restoreHandler->handle(new RestoreUserCommand($uuid));

        return response()->json(['message' => 'User restored successfully.']);
    }
}
