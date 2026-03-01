<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\RestoreProduct;

class RestoreProductCommand
{
    public function __construct(
        public string $uuid
    ) {}
}
