<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\DeleteProduct;

class DeleteProductCommand
{
    public function __construct(
        public string $uuid
    ) {}
}
