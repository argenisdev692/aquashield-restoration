<?php

declare(strict_types=1);

namespace Modules\Users\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Users\Domain\Ports\UserAuditPort;
use Modules\Users\Domain\Ports\UserCachePort;
use Modules\Users\Domain\Ports\UserPhoneNormalizerPort;
use Modules\Users\Domain\Ports\UserRepositoryPort;
use Modules\Users\Domain\Ports\UserProfileRepositoryPort;
use Modules\Users\Infrastructure\ExternalServices\Audit\UserAuditAdapter;
use Modules\Users\Infrastructure\ExternalServices\Cache\LaravelUserCacheAdapter;
use Modules\Users\Infrastructure\ExternalServices\Phone\UserPhoneNormalizerAdapter;
use Modules\Users\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Modules\Users\Infrastructure\Persistence\Repositories\EloquentUserProfileRepository;

/**
 * UsersServiceProvider — Binds Users context ports to their infrastructure adapters.
 */
final class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Domain Ports → Infrastructure Adapters ──
        $this->app->bind(UserAuditPort::class, UserAuditAdapter::class);
        $this->app->bind(UserCachePort::class, LaravelUserCacheAdapter::class);
        $this->app->bind(UserPhoneNormalizerPort::class, UserPhoneNormalizerAdapter::class);
        $this->app->bind(UserRepositoryPort::class, EloquentUserRepository::class);
        $this->app->bind(UserProfileRepositoryPort::class, EloquentUserProfileRepository::class);
        $this->app->bind(\Modules\Users\Domain\Ports\StoragePort::class, \Modules\Users\Infrastructure\ExternalServices\Storage\AvatarStorageAdapter::class);
    }

    public function boot(): void
    {
        // ── Context-specific Resources ──
        $this->loadMigrationsFrom(__DIR__ . '/../Infrastructure/Persistence/Eloquent/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Users\Infrastructure\CLI\ResendSetupEmailCommand::class,
            ]);
        }

        // ── Context-specific Route Loading ──
        $this->registerWebRoutes();
        $this->registerApiRoutes();
    }

    private function registerWebRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('users')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }

    private function registerApiRoutes(): void
    {
        Route::middleware(['api'])
            ->prefix('api/users')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
