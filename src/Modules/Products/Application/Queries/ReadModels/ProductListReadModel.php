<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Queries\ReadModels;

class ProductListReadModel
{
    public function __construct(
        public string $uuid,
        public string $categoryName,
        public string $name,
        public float $price,
        public string $unit,
        public int $orderPosition,
        public string $createdAt,
        public ?string $deletedAt = null
    ) {}
}
