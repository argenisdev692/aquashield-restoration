<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Repositories\EloquentInsuranceCompanyRepository;

final class InsuranceCompaniesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            InsuranceCompanyRepositoryPort::class,
            EloquentInsuranceCompanyRepository::class
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
            ->prefix('insurance-companies')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }

    private function registerApiRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/insurance-companies')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
