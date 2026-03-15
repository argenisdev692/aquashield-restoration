<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Application\Queries;

use Src\Modules\CategoryProducts\Application\Queries\ReadModels\CategoryProductReadModel;
use Src\Modules\CategoryProducts\Domain\Ports\CategoryProductRepositoryPort;
use Src\Modules\CategoryProducts\Domain\ValueObjects\CategoryProductId;

final class GetCategoryProductHandler
{
    public function __construct(
        private readonly CategoryProductRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): ?CategoryProductReadModel
    {
        $categoryProduct = $this->repository->find(CategoryProductId::fromString($uuid));

        if ($categoryProduct === null) {
            return null;
        }

        return new CategoryProductReadModel(
            uuid: $categoryProduct->id()->toString(),
            categoryProductName: $categoryProduct->categoryProductName(),
            createdAt: $categoryProduct->createdAt(),
            updatedAt: $categoryProduct->updatedAt(),
            deletedAt: $categoryProduct->deletedAt(),
        );
    }
}
