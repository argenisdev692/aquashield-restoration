<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\CategoryProducts\Domain\Ports\CategoryProductRepositoryPort;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Repositories\EloquentCategoryProductRepository;

final class CategoryProductsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CategoryProductRepositoryPort::class,
            EloquentCategoryProductRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
