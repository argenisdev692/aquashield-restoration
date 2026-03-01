<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Commands\CreateProduct;

class CreateProductCommand
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public string $description,
        public float $price,
        public string $unit,
        public int $orderPosition
    ) {}
}
