<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Application\Commands;

use Src\Modules\ServiceCategories\Application\DTOs\StoreServiceCategoryData;
use Src\Modules\ServiceCategories\Domain\Entities\ServiceCategory;
use Src\Modules\ServiceCategories\Domain\Ports\ServiceCategoryRepositoryPort;
use Src\Modules\ServiceCategories\Domain\ValueObjects\ServiceCategoryId;

final class CreateServiceCategoryHandler
{
    public function __construct(
        private readonly ServiceCategoryRepositoryPort $repository,
    ) {}

    public function handle(StoreServiceCategoryData $data): string
    {
        $id = ServiceCategoryId::generate();
        $serviceCategory = ServiceCategory::create(
            id: $id,
            category: $data->category,
            type: $data->type,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($serviceCategory);

        return $id->toString();
    }
}
