<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\UpdateProduct;

class UpdateProductCommand
{
    public function __construct(
        public string $uuid,
        public string $categoryId,
        public string $name,
        public string $description,
        public float $price,
        public string $unit,
        public int $orderPosition
    ) {}
}
