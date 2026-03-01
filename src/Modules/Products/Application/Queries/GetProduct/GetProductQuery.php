<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Queries\GetProduct;

class GetProductQuery
{
    public function __construct(
        public string $uuid
    ) {}
}
