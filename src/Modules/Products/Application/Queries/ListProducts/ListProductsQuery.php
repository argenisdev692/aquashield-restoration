<?php

declare(strict_types=1);

namespace Src\Modules\Products\Application\Queries\ListProducts;

use Src\Modules\Products\Application\DTOs\ProductFilterDTO;

class ListProductsQuery
{
    public function __construct(
        public ProductFilterDTO $filters
    ) {}
}
