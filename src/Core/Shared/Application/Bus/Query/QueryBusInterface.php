<?php

declare(strict_types=1);

namespace Src\Core\Shared\Application\Bus\Query;

interface QueryBusInterface
{
    public function ask(object $query): mixed;

    public function map(array $map): void;
}
