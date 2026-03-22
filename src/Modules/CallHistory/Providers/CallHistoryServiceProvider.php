<?php

declare(strict_types=1);

namespace Modules\CallHistory\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CallHistory\Application\Commands\BulkDeleteCallHistoryCommand;
use Modules\CallHistory\Application\Commands\CreateCallHistoryCommand;
use Modules\CallHistory\Application\Commands\DeleteCallHistoryCommand;
use Modules\CallHistory\Application\Commands\RestoreCallHistoryCommand;
use Modules\CallHistory\Application\Commands\SyncCallsFromRetellCommand;
use Modules\CallHistory\Application\Commands\UpdateCallHistoryCommand;
use Modules\CallHistory\Application\Queries\GetCallHistoryQuery;
use Modules\CallHistory\Application\Queries\ListCallHistoryQuery;
use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;
use Modules\CallHistory\Infrastructure\Persistence\Eloquent\Mappers\CallHistoryMapper;
use Modules\CallHistory\Infrastructure\Persistence\Eloquent\Repositories\CallHistoryEloquentRepository;
use Modules\CallHistory\Infrastructure\Services\RetellAIService;

final class CallHistoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CallHistoryRepositoryPort::class, CallHistoryEloquentRepository::class);
        $this->app->singleton(CallHistoryMapper::class);
        $this->app->singleton(RetellAIService::class);

        $this->app->singleton(CreateCallHistoryCommand::class, function ($app) {
            return new CreateCallHistoryCommand(
                $app->make(CallHistoryRepositoryPort::class)
            );
        });

        $this->app->singleton(UpdateCallHistoryCommand::class, function ($app) {
            return new UpdateCallHistoryCommand(
                $app->make(CallHistoryRepositoryPort::class)
            );
        });

        $this->app->singleton(DeleteCallHistoryCommand::class, function ($app) {
            return new DeleteCallHistoryCommand(
                $app->make(CallHistoryRepositoryPort::class)
            );
        });

        $this->app->singleton(RestoreCallHistoryCommand::class, function ($app) {
            return new RestoreCallHistoryCommand(
                $app->make(CallHistoryRepositoryPort::class)
            );
        });

        $this->app->singleton(BulkDeleteCallHistoryCommand::class, function ($app) {
            return new BulkDeleteCallHistoryCommand(
                $app->make(CallHistoryRepositoryPort::class)
            );
        });

        $this->app->singleton(GetCallHistoryQuery::class, function ($app) {
            return new GetCallHistoryQuery(
                $app->make(CallHistoryRepositoryPort::class)
            );
        });

        $this->app->singleton(ListCallHistoryQuery::class, function ($app) {
            return new ListCallHistoryQuery(
                $app->make(CallHistoryRepositoryPort::class)
            );
        });

        $this->app->singleton(SyncCallsFromRetellCommand::class, function ($app) {
            return new SyncCallsFromRetellCommand(
                $app->make(CallHistoryRepositoryPort::class),
                $app->make(RetellAIService::class),
                $app->make(CreateCallHistoryCommand::class),
                $app->make(UpdateCallHistoryCommand::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Infrastructure/Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Infrastructure/Routes/api.php');
    }
}
