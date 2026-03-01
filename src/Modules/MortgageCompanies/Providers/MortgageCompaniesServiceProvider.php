<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Infrastructure\Persistence\Repositories\EloquentMortgageCompanyRepository;

final class MortgageCompaniesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            MortgageCompanyRepositoryPort::class,
            EloquentMortgageCompanyRepository::class
        );
    }

    public function boot(): void
    {
        $this->registerWebRoutes();
    }

    private function registerWebRoutes(): void
    {
        Route::middleware(['web'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
