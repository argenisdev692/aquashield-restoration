<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\ScopeSheets\Application\Queries\Contracts\ScopeSheetReadRepository;
use Src\Modules\ScopeSheets\Domain\Ports\ScopeSheetRepositoryPort;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Mappers\ScopeSheetMapper;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\ReadRepositories\EloquentScopeSheetReadRepository;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Repositories\EloquentScopeSheetRepository;

final class ScopeSheetsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ScopeSheetRepositoryPort::class,
            static fn ($app): EloquentScopeSheetRepository => new EloquentScopeSheetRepository(
                $app->make(ScopeSheetMapper::class),
            ),
        );

        $this->app->bind(
            ScopeSheetReadRepository::class,
            EloquentScopeSheetReadRepository::class,
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
            ->prefix('api/scope-sheets')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
