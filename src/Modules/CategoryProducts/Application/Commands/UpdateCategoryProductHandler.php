<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Application\Commands;

use RuntimeException;
use Src\Modules\CategoryProducts\Application\DTOs\UpdateCategoryProductData;
use Src\Modules\CategoryProducts\Domain\Ports\CategoryProductRepositoryPort;
use Src\Modules\CategoryProducts\Domain\ValueObjects\CategoryProductId;

final class UpdateCategoryProductHandler
{
    public function __construct(
        private readonly CategoryProductRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateCategoryProductData $data): void
    {
        $categoryProductId = CategoryProductId::fromString($uuid);
        $categoryProduct = $this->repository->find($categoryProductId);

        if ($categoryProduct === null) {
            throw new RuntimeException('Category product not found.');
        }

        $categoryProduct->update(
            categoryProductName: $data->categoryProductName,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($categoryProduct);
    }
}
