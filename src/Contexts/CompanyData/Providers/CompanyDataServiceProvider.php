<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Providers;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Contexts\CompanyData\Application\Commands\CreateCompanyData\CreateCompanyDataCommand;
use Src\Contexts\CompanyData\Application\Commands\CreateCompanyData\CreateCompanyDataHandler;
use Src\Contexts\CompanyData\Application\Commands\DeleteCompanyData\DeleteCompanyDataCommand;
use Src\Contexts\CompanyData\Application\Commands\DeleteCompanyData\DeleteCompanyDataHandler;
use Src\Contexts\CompanyData\Application\Commands\RestoreCompanyData\RestoreCompanyDataCommand;
use Src\Contexts\CompanyData\Application\Commands\RestoreCompanyData\RestoreCompanyDataHandler;
use Src\Contexts\CompanyData\Application\Commands\UpdateCompanyData\UpdateCompanyDataCommand;
use Src\Contexts\CompanyData\Application\Commands\UpdateCompanyData\UpdateCompanyDataHandler;
use Src\Contexts\CompanyData\Application\Queries\GetCompanyData\GetCompanyDataHandler;
use Src\Contexts\CompanyData\Application\Queries\GetCompanyData\GetCompanyDataQuery;
use Src\Contexts\CompanyData\Application\Queries\ListCompanyData\ListCompanyDataHandler;
use Src\Contexts\CompanyData\Application\Queries\ListCompanyData\ListCompanyDataQuery;
use Src\Contexts\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Src\Contexts\CompanyData\Infrastructure\Persistence\Repositories\EloquentCompanyDataRepository;

final class CompanyDataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyDataRepositoryPort::class, EloquentCompanyDataRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutes();

        Bus::map([
            CreateCompanyDataCommand::class => CreateCompanyDataHandler::class,
            UpdateCompanyDataCommand::class => UpdateCompanyDataHandler::class,
            DeleteCompanyDataCommand::class => DeleteCompanyDataHandler::class,
            RestoreCompanyDataCommand::class => RestoreCompanyDataHandler::class,
            GetCompanyDataQuery::class => GetCompanyDataHandler::class,
            ListCompanyDataQuery::class => ListCompanyDataHandler::class,
        ]);
    }

    private function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Infrastructure/Routes/web.php');

        Route::prefix('api/company-data')
            ->middleware(['web', 'auth'])
            ->group(base_path('src/Contexts/CompanyData/Infrastructure/Routes/api.php'));
    }

}
