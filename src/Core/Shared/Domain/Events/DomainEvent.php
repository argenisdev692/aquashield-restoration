<?php

declare(strict_types=1);

namespace Src\Core\Shared\Domain\Events;

use DateTimeImmutable;

abstract readonly class DomainEvent
{
    public DateTimeImmutable $occurredOn;

    public function __construct()
    {
        $this->occurredOn = new DateTimeImmutable();
    }
}
