<?php

declare(strict_types=1);

namespace Modules\AccessControl\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AccessControl\Domain\Ports\AccessControlAuditPort;
use Modules\AccessControl\Domain\Ports\AccessControlRepositoryPort;
use Modules\AccessControl\Infrastructure\ExternalServices\Audit\AccessControlAuditAdapter;
use Modules\AccessControl\Infrastructure\Persistence\Repositories\EloquentAccessControlRepository;

final class AccessControlServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccessControlAuditPort::class, AccessControlAuditAdapter::class);
        $this->app->bind(AccessControlRepositoryPort::class, EloquentAccessControlRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Infrastructure/Persistence/Eloquent/Migrations');
        $this->registerWebRoutes();
    }

    private function registerWebRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('permissions')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
