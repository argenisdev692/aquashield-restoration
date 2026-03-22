<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\ClaimStatuses\Domain\Ports\ClaimStatusRepositoryPort;
use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Repositories\EloquentClaimStatusRepository;

final class ClaimStatusesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ClaimStatusRepositoryPort::class,
            EloquentClaimStatusRepository::class,
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
            ->prefix('api/claim-statuses')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
