<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Infrastructure\Adapters\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Src\Contexts\Users\Application\Commands\DeleteUser\DeleteUserCommand;
use Src\Contexts\Users\Application\Commands\DeleteUser\DeleteUserHandler;
use Src\Contexts\Users\Application\Queries\GetUser\GetUserHandler;
use Src\Contexts\Users\Application\Queries\GetUser\GetUserQuery;
use Src\Contexts\Users\Domain\Ports\UserRepositoryPort;
use Src\Contexts\Users\Infrastructure\Adapters\Http\Resources\UserResource;

/**
 * UserPageController — Inertia page renders + write actions for Users CRUD.
 */
final class UserPageController
{
    public function __construct(
        private readonly GetUserHandler $getHandler,
        private readonly DeleteUserHandler $deleteHandler,
        private readonly UserRepositoryPort $repository,
    ) {
    }

    /**
     * GET /users — Index page.
     */
    public function index(): Response
    {
        return Inertia::render('users/UsersIndexPage');
    }

    /**
     * GET /users/create — Create form.
     */
    public function create(): Response
    {
        return Inertia::render('users/UserCreatePage');
    }

    /**
     * GET /users/{uuid} — Show page.
     */
    public function show(string $uuid): Response
    {
        $user = $this->getHandler->handle(new GetUserQuery($uuid));

        return Inertia::render('users/UserShowPage', [
            'user' => new UserResource($user),
        ]);
    }

    /**
     * GET /users/{uuid}/edit — Edit form.
     */
    public function edit(string $uuid): Response
    {
        $user = $this->getHandler->handle(new GetUserQuery($uuid));

        return Inertia::render('users/UserEditPage', [
            'user' => new UserResource($user),
        ]);
    }

    /**
     * DELETE /users/{uuid} — Soft-delete a user.
     */
    public function destroy(string $uuid): RedirectResponse
    {
        $this->deleteHandler->handle(new DeleteUserCommand($uuid));

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * PATCH /users/{uuid}/restore — Restore a soft-deleted user.
     */
    public function restore(string $uuid): RedirectResponse
    {
        $this->repository->restore($uuid);

        return redirect()->route('users.index')->with('success', 'User restored successfully.');
    }
}
