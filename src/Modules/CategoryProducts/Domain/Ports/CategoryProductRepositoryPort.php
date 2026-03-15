<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Domain\Ports;

use Src\Modules\CategoryProducts\Domain\Entities\CategoryProduct;
use Src\Modules\CategoryProducts\Domain\ValueObjects\CategoryProductId;

interface CategoryProductRepositoryPort
{
    public function find(CategoryProductId $id): ?CategoryProduct;

    public function save(CategoryProduct $categoryProduct): void;

    public function softDelete(CategoryProductId $id): void;

    public function restore(CategoryProductId $id): void;

    public function bulkSoftDelete(array $ids): int;
}
