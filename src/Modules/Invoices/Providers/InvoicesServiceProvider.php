<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\Invoices\Application\Queries\Contracts\InvoiceReadRepository;
use Src\Modules\Invoices\Domain\Ports\InvoiceRepositoryPort;
use Src\Modules\Invoices\Infrastructure\Persistence\Mappers\InvoiceMapper;
use Src\Modules\Invoices\Infrastructure\Persistence\ReadRepositories\EloquentInvoiceReadRepository;
use Src\Modules\Invoices\Infrastructure\Persistence\Repositories\EloquentInvoiceRepository;

final class InvoicesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            InvoiceRepositoryPort::class,
            static fn ($app): EloquentInvoiceRepository => new EloquentInvoiceRepository(
                $app->make(InvoiceMapper::class),
            ),
        );

        $this->app->bind(
            InvoiceReadRepository::class,
            EloquentInvoiceReadRepository::class,
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
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }

    private function registerApiRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/invoices')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
