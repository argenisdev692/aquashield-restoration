<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Application\Commands;

use Src\Modules\ServiceCategories\Application\DTOs\BulkDeleteServiceCategoryData;
use Src\Modules\ServiceCategories\Domain\Ports\ServiceCategoryRepositoryPort;
use Src\Modules\ServiceCategories\Domain\ValueObjects\ServiceCategoryId;

final class BulkDeleteServiceCategoryHandler
{
    public function __construct(
        private readonly ServiceCategoryRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteServiceCategoryData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): ServiceCategoryId => ServiceCategoryId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}
