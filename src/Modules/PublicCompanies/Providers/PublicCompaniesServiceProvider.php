<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\PublicCompanies\Application\Queries\Contracts\PublicCompanyReadRepository;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Infrastructure\Persistence\ReadRepositories\EloquentPublicCompanyReadRepository;
use Modules\PublicCompanies\Infrastructure\Persistence\Repositories\EloquentPublicCompanyRepository;

final class PublicCompaniesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PublicCompanyRepositoryPort::class,
            EloquentPublicCompanyRepository::class,
        );

        $this->app->bind(
            PublicCompanyReadRepository::class,
            EloquentPublicCompanyReadRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
