<?php

declare(strict_types=1);

namespace Src\Modules\Products\Domain\Ports;

use Src\Modules\Products\Domain\Entities\Product;
use Src\Modules\Products\Domain\ValueObjects\ProductId;

interface ProductRepositoryPort
{
    public function find(ProductId $id): ?Product;

    public function save(Product $product): void;

    public function softDelete(ProductId $id): void;

    public function restore(ProductId $id): void;
}
