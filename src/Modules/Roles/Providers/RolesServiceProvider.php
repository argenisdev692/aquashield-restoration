<?php

declare(strict_types=1);

namespace Modules\Roles\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Roles\Domain\Ports\RoleRepositoryPort;
use Modules\Roles\Infrastructure\Persistence\Repositories\EloquentRoleRepository;

final class RolesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RoleRepositoryPort::class, EloquentRoleRepository::class);
    }

    public function boot(): void
    {
        $this->registerWebRoutes();
    }

    private function registerWebRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('roles')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
