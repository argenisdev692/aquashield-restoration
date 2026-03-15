<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\ContactSupports\Domain\Ports\ContactSupportRepositoryPort;
use Src\Modules\ContactSupports\Infrastructure\Persistence\Repositories\EloquentContactSupportRepository;

final class ContactSupportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ContactSupportRepositoryPort::class,
            EloquentContactSupportRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
