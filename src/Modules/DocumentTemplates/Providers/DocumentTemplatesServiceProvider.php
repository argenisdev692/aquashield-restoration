<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;
use Src\Modules\DocumentTemplates\Domain\Ports\DocumentTemplateRepositoryPort;
use Src\Modules\DocumentTemplates\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Repositories\EloquentDocumentTemplateRepository;
use Src\Modules\DocumentTemplates\Infrastructure\Storage\R2DocumentTemplateStorageAdapter;

final class DocumentTemplatesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DocumentTemplateRepositoryPort::class,
            EloquentDocumentTemplateRepository::class,
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
            ->prefix('document-templates')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/document-templates')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
