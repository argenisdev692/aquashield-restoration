<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\ProjectTypes\Domain\Ports\ProjectTypeRepositoryPort;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Repositories\EloquentProjectTypeRepository;

final class ProjectTypesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProjectTypeRepositoryPort::class,
            EloquentProjectTypeRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
