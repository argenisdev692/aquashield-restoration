<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\Portfolios\Domain\Ports\PortfolioRepositoryPort;
use Src\Modules\Portfolios\Infrastructure\Persistence\Repositories\EloquentPortfolioRepository;

final class PortfoliosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PortfolioRepositoryPort::class,
            EloquentPortfolioRepository::class,
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
            ->prefix('api/portfolios')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
