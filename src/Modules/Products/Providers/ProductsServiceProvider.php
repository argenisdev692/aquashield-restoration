<?php

declare(strict_types=1);

namespace Src\Modules\Products\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\Products\Domain\Ports\ProductRepositoryPort;
use Src\Modules\Products\Infrastructure\Persistence\Repositories\EloquentProductRepository;

class ProductsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryPort::class,
            EloquentProductRepository::class
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
            ->prefix('api')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
