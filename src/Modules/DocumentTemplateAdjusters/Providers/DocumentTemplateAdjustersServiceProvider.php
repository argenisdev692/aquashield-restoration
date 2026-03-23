<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;
use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\DocumentTemplateAdjusterRepositoryPort;
use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Repositories\EloquentDocumentTemplateAdjusterRepository;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Storage\R2DocumentTemplateAdjusterStorageAdapter;

final class DocumentTemplateAdjustersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DocumentTemplateAdjusterRepositoryPort::class,
            EloquentDocumentTemplateAdjusterRepository::class,
        );

        $this->app->bind(StoragePort::class, static function ($app): R2DocumentTemplateAdjusterStorageAdapter {
            return new R2DocumentTemplateAdjusterStorageAdapter(
                $app->make(CircuitBreakerInterface::class),
            );
        });
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('document-template-adjusters')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/document-template-adjusters')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
