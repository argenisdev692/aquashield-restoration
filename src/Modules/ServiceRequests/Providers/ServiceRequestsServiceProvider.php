<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\ServiceRequests\Domain\Ports\ServiceRequestRepositoryPort;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Repositories\EloquentServiceRequestRepository;

final class ServiceRequestsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ServiceRequestRepositoryPort::class,
            EloquentServiceRequestRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/service-requests')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
