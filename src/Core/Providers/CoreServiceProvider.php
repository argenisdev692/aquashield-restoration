<?php

declare(strict_types=1);

namespace Src\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Core\Shared\Infrastructure\Observability\HealthCheck\DatabaseHealthCheck;
use Src\Core\Shared\Infrastructure\Observability\HealthCheck\HealthCheckAggregator;
use Src\Core\Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;
use Src\Core\Shared\Infrastructure\Resilience\CircuitBreaker\RedisCircuitBreaker;
use Src\Core\Shared\Infrastructure\Persistence\Transactions\TransactionInterface;
use Src\Core\Shared\Infrastructure\Persistence\Transactions\DatabaseTransaction;
use Src\Core\Shared\Infrastructure\Audit\AuditInterface;
use Src\Core\Shared\Infrastructure\Audit\SpatieAuditAdapter;
use Src\Core\Shared\Infrastructure\Resilience\RateLimiter\CustomRateLimiter;
use Src\Core\Shared\Infrastructure\Export\ExportInterface;
use Src\Core\Shared\Infrastructure\Export\LaravelExportAdapter;

final class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(BusServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        $this->app->singleton(HealthCheckAggregator::class, function () {
            $aggregator = new HealthCheckAggregator();
            $aggregator->addCheck(new DatabaseHealthCheck());
            return $aggregator;
        });

        $this->app->bind(CircuitBreakerInterface::class, RedisCircuitBreaker::class);
        $this->app->bind(TransactionInterface::class, DatabaseTransaction::class);
        $this->app->bind(AuditInterface::class, SpatieAuditAdapter::class);
        $this->app->bind(ExportInterface::class, LaravelExportAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadHealthRoute();
    }

    private function loadHealthRoute(): void
    {
        \Illuminate\Support\Facades\Route::get('/health', \Src\Core\Shared\Infrastructure\Observability\HealthCheck\HealthCheckController::class)
            ->middleware('api');
    }
}
