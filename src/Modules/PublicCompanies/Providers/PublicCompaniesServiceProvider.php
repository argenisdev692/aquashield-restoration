<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Infrastructure\Persistence\Repositories\EloquentPublicCompanyRepository;

final class PublicCompaniesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PublicCompanyRepositoryPort::class,
            EloquentPublicCompanyRepository::class
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
            ->prefix('public-companies')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }

    private function registerApiRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/public-companies')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
