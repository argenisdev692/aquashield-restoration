<?php

declare(strict_types=1);

namespace Src\Core\Shared\Application\Bus\Command;

interface CommandBusInterface
{
    public function dispatch(object $command): void;

    public function map(array $map): void;
}
