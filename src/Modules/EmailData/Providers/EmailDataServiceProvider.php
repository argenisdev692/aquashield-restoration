<?php

declare(strict_types=1);

namespace Modules\EmailData\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\EmailData\Domain\Ports\EmailDataRepositoryPort;
use Modules\EmailData\Infrastructure\Persistence\Repositories\EloquentEmailDataRepository;

final class EmailDataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            EmailDataRepositoryPort::class,
            EloquentEmailDataRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
