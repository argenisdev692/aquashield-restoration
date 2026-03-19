<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Infrastructure\Persistence\Repositories\EloquentAllianceCompanyRepository;

final class AllianceCompaniesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AllianceCompanyRepositoryPort::class,
            EloquentAllianceCompanyRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('alliance-companies')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
