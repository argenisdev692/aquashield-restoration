<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Application\Commands;

use Src\Modules\CategoryProducts\Application\DTOs\StoreCategoryProductData;
use Src\Modules\CategoryProducts\Domain\Entities\CategoryProduct;
use Src\Modules\CategoryProducts\Domain\Ports\CategoryProductRepositoryPort;
use Src\Modules\CategoryProducts\Domain\ValueObjects\CategoryProductId;

final class CreateCategoryProductHandler
{
    public function __construct(
        private readonly CategoryProductRepositoryPort $repository,
    ) {}

    public function handle(StoreCategoryProductData $data): string
    {
        $id = CategoryProductId::generate();
        $categoryProduct = CategoryProduct::create(
            id: $id,
            categoryProductName: $data->categoryProductName,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($categoryProduct);

        return $id->toString();
    }
}
