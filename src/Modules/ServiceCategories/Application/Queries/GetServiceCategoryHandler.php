<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Application\Queries;

use Src\Modules\ServiceCategories\Application\Queries\ReadModels\ServiceCategoryReadModel;
use Src\Modules\ServiceCategories\Domain\Ports\ServiceCategoryRepositoryPort;
use Src\Modules\ServiceCategories\Domain\ValueObjects\ServiceCategoryId;

final class GetServiceCategoryHandler
{
    public function __construct(
        private readonly ServiceCategoryRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): ?ServiceCategoryReadModel
    {
        $serviceCategory = $this->repository->find(ServiceCategoryId::fromString($uuid));

        if ($serviceCategory === null) {
            return null;
        }

        return new ServiceCategoryReadModel(
            uuid: $serviceCategory->id()->toString(),
            category: $serviceCategory->category(),
            type: $serviceCategory->type(),
            createdAt: $serviceCategory->createdAt(),
            updatedAt: $serviceCategory->updatedAt(),
            deletedAt: $serviceCategory->deletedAt(),
        );
    }
}
