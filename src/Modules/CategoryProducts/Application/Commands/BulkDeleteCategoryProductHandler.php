<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Application\Commands;

use Src\Modules\CategoryProducts\Application\DTOs\BulkDeleteCategoryProductData;
use Src\Modules\CategoryProducts\Domain\Ports\CategoryProductRepositoryPort;
use Src\Modules\CategoryProducts\Domain\ValueObjects\CategoryProductId;

final class BulkDeleteCategoryProductHandler
{
    public function __construct(
        private readonly CategoryProductRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteCategoryProductData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): CategoryProductId => CategoryProductId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
