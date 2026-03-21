<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\MortgageCompanies\Application\Queries\Contracts\MortgageCompanyReadRepository;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Infrastructure\Persistence\ReadRepositories\EloquentMortgageCompanyReadRepository;
use Modules\MortgageCompanies\Infrastructure\Persistence\Repositories\EloquentMortgageCompanyRepository;

final class MortgageCompaniesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            MortgageCompanyRepositoryPort::class,
            EloquentMortgageCompanyRepository::class,
        );

        $this->app->bind(
            MortgageCompanyReadRepository::class,
            EloquentMortgageCompanyReadRepository::class,
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
            ->prefix('mortgage-companies')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }

    private function registerApiRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/mortgage-companies')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
