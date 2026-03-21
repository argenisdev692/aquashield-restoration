<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\ServiceCategories\Domain\Ports\ServiceCategoryRepositoryPort;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Repositories\EloquentServiceCategoryRepository;

final class ServiceCategoriesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ServiceCategoryRepositoryPort::class,
            EloquentServiceCategoryRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
