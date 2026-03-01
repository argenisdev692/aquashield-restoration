<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\DTOs;

use Spatie\LaravelData\Data;

class ProductFilterDTO extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $categoryId = null,
        public ?string $status = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public int $page = 1,
        public int $perPage = 15
    ) {}
}
