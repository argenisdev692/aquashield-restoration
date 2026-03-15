<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\CauseOfLosses\Domain\Ports\CauseOfLossRepositoryPort;
use Src\Modules\CauseOfLosses\Infrastructure\Persistence\Repositories\EloquentCauseOfLossRepository;

final class CauseOfLossesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CauseOfLossRepositoryPort::class,
            EloquentCauseOfLossRepository::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}
