<?php

declare(strict_types=1);

namespace Src\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Core\Shared\Application\Bus\Command\CommandBusInterface;
use Src\Core\Shared\Application\Bus\Command\SyncCommandBus;
use Src\Core\Shared\Application\Bus\Query\QueryBusInterface;
use Src\Core\Shared\Application\Bus\Query\SyncQueryBus;

final class BusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CommandBusInterface::class, SyncCommandBus::class);
        $this->app->singleton(QueryBusInterface::class, SyncQueryBus::class);
    }
}
