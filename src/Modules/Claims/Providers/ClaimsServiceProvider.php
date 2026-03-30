<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\Claims\Application\Queries\Contracts\ClaimReadRepository;
use Src\Modules\Claims\Domain\Ports\ClaimInternalIdGeneratorPort;
use Src\Modules\Claims\Domain\Ports\ClaimRepositoryPort;
use Src\Modules\Claims\Infrastructure\Persistence\Generators\EloquentClaimInternalIdGenerator;
use Src\Modules\Claims\Infrastructure\Persistence\Mappers\ClaimMapper;
use Src\Modules\Claims\Infrastructure\Persistence\ReadRepositories\EloquentClaimReadRepository;
use Src\Modules\Claims\Infrastructure\Persistence\Repositories\EloquentClaimRepository;

final class ClaimsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ClaimRepositoryPort::class,
            static fn ($app): EloquentClaimRepository => new EloquentClaimRepository(
                $app->make(ClaimMapper::class),
            ),
        );

        $this->app->bind(
            ClaimReadRepository::class,
            EloquentClaimReadRepository::class,
        );

        $this->app->bind(
            ClaimInternalIdGeneratorPort::class,
            EloquentClaimInternalIdGenerator::class,
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
            ->prefix('api/claims')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
