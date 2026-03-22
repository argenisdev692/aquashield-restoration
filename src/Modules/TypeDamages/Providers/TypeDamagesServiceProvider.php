<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\TypeDamages\Domain\Ports\TypeDamageRepositoryPort;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Repositories\EloquentTypeDamageRepository;

final class TypeDamagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TypeDamageRepositoryPort::class,
            EloquentTypeDamageRepository::class,
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
            ->prefix('api/type-damages')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
