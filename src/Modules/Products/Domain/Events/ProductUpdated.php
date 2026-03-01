<?php

declare(strict_types=1);

namespace Src\Modules\Products\Domain\Events;

use Src\Modules\Products\Domain\ValueObjects\ProductId;
use Src\Shared\Domain\DomainEvent;

readonly class ProductUpdated extends DomainEvent
{
    public function __construct(
        public ProductId $productId,
        public string $name,
        public string $occurredOn
    ) {}
}
