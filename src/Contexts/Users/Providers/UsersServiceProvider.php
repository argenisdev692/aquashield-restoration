<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Contexts\Users\Domain\Ports\UserRepositoryPort;
use Src\Contexts\Users\Infrastructure\Persistence\Repositories\EloquentUserRepository;

/**
 * UsersServiceProvider — Binds Users context ports to their infrastructure adapters.
 *
 * ── Port → Adapter Bindings ─────────────────────────────
 * • UserRepositoryPort → EloquentUserRepository
 *
 * ⚠️  A Port without a binding compiles silently but throws a
 *     fatal container resolution error at runtime.
 *
 * Must be registered in bootstrap/providers.php.
 */
final class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Domain Ports → Infrastructure Adapters ──
        $this->app->bind(UserRepositoryPort::class, EloquentUserRepository::class);
    }

    public function boot(): void
    {
        // ── Context-specific Route Loading ──
        Route::middleware(['web', 'auth'])
            ->prefix('users')
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');

        Route::middleware(['web', 'auth'])
            ->prefix('api/users')
            ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
    }

}
