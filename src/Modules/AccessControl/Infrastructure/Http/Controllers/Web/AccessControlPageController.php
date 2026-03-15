<?php

declare(strict_types=1);

namespace Modules\AccessControl\Infrastructure\Http\Controllers\Web;

use Inertia\Inertia;
use Inertia\Response;

final class AccessControlPageController
{
    public function index(): Response
    {
        return Inertia::render('permissions/PermissionsIndexPage');
    }
}
