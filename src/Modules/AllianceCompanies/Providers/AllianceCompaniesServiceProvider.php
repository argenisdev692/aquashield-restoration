<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Infrastructure\Persistence\Repositories\EloquentAllianceCompanyRepository;

final class AllianceCompaniesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AllianceCompanyRepositoryPort::class,
            EloquentAllianceCompanyRepository::class
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
            ->prefix('alliance-companies')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }

    private function registerApiRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/alliance-companies')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
