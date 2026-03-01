<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Queries\ReadModels;

class ProductReadModel
{
    public function __construct(
        public string $uuid,
        public string $categoryId,
        public string $categoryName,
        public string $name,
        public string $description,
        public float $price,
        public string $unit,
        public int $orderPosition,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null
    ) {}
}
