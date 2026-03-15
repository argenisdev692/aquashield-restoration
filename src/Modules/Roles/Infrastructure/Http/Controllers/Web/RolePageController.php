<?php

declare(strict_types=1);

namespace Modules\Roles\Infrastructure\Http\Controllers\Web;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Roles\Domain\Ports\RoleRepositoryPort;
use Modules\Roles\Infrastructure\Http\Resources\RoleResource;

final readonly class RolePageController
{
    public function __construct(
        private RoleRepositoryPort $roles,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('roles/RolesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('roles/RoleCreatePage');
    }

    public function edit(string $uuid): Response
    {
        $role = $this->roles->findByUuid($uuid);
        abort_if($role === null, 404);

        return Inertia::render('roles/RoleEditPage', [
            'role' => RoleResource::make($role)->resolve(request()),
        ]);
    }
}
