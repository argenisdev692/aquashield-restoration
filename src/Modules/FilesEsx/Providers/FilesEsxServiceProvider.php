<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;
use Src\Modules\FilesEsx\Application\Queries\Contracts\FileEsxReadRepository;
use Src\Modules\FilesEsx\Domain\Ports\FileAssignmentRepositoryPort;
use Src\Modules\FilesEsx\Domain\Ports\FileEsxRepositoryPort;
use Src\Modules\FilesEsx\Domain\Ports\FileStoragePort;
use Src\Modules\FilesEsx\Infrastructure\ExternalServices\Storage\FileEsxStorageAdapter;
use Src\Modules\FilesEsx\Infrastructure\Persistence\ReadRepositories\EloquentFileEsxReadRepository;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Repositories\EloquentFileAssignmentRepository;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Repositories\EloquentFileEsxRepository;

final class FilesEsxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            FileEsxRepositoryPort::class,
            EloquentFileEsxRepository::class,
        );

        $this->app->bind(
            FileAssignmentRepositoryPort::class,
            EloquentFileAssignmentRepository::class,
        );

        $this->app->bind(
            FileStoragePort::class,
            static fn ($app): FileEsxStorageAdapter => new FileEsxStorageAdapter(
                $app->make(CircuitBreakerInterface::class),
            ),
        );

        $this->app->bind(
            FileEsxReadRepository::class,
            static fn ($app): EloquentFileEsxReadRepository => new EloquentFileEsxReadRepository(
                $app->make(FileStoragePort::class),
            ),
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
            ->prefix('api/files-esx')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
