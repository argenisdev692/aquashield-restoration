<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;
use Src\Modules\DocumentTemplateAlliances\Domain\Ports\DocumentTemplateAllianceRepositoryPort;
use Src\Modules\DocumentTemplateAlliances\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Repositories\EloquentDocumentTemplateAllianceRepository;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Storage\R2DocumentTemplateStorageAdapter;

final class DocumentTemplateAlliancesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DocumentTemplateAllianceRepositoryPort::class,
            EloquentDocumentTemplateAllianceRepository::class,
        );

        $this->app->bind(StoragePort::class, static function ($app): R2DocumentTemplateStorageAdapter {
            return new R2DocumentTemplateStorageAdapter(
                $app->make(CircuitBreakerInterface::class),
            );
        });
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('document-template-alliances')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/document-template-alliances')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
