<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\Zones\Domain\Ports\ZoneRepositoryPort;
use Src\Modules\Zones\Infrastructure\Persistence\Repositories\EloquentZoneRepository;

final class ZonesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ZoneRepositoryPort::class,
            EloquentZoneRepository::class,
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
            ->prefix('api/zones')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
