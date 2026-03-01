<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\DTOs;

use Spatie\LaravelData\Data;

class ProductDTO extends Data
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
