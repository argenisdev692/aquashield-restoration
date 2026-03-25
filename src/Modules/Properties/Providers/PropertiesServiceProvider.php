<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\Properties\Domain\Ports\PropertyRepositoryPort;
use Src\Modules\Properties\Infrastructure\Persistence\Repositories\EloquentPropertyRepository;

final class PropertiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PropertyRepositoryPort::class,
            EloquentPropertyRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/properties')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
