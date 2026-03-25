<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\Customers\Domain\Ports\CustomerRepositoryPort;
use Src\Modules\Customers\Infrastructure\Persistence\Repositories\EloquentCustomerRepository;

final class CustomersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CustomerRepositoryPort::class,
            EloquentCustomerRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/customers')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
