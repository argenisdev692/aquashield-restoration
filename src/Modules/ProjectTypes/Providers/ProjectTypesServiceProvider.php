<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\ProjectTypes\Domain\Ports\ProjectTypeRepositoryPort;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Repositories\EloquentProjectTypeRepository;

final class ProjectTypesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProjectTypeRepositoryPort::class,
            EloquentProjectTypeRepository::class,
        );
    }

    public function boot(): void
    {
        $this->registerWebRoutes();
        $this->registerApiRoutes();
    }

    private function registerWebRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }

    private function registerApiRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/project-types')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
