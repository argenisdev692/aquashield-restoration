<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Repositories\EloquentInsuranceCompanyRepository;

final class InsuranceCompaniesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            InsuranceCompanyRepositoryPort::class,
            EloquentInsuranceCompanyRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
