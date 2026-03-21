<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Application\Commands;

use RuntimeException;
use Src\Modules\ServiceCategories\Application\DTOs\UpdateServiceCategoryData;
use Src\Modules\ServiceCategories\Domain\Ports\ServiceCategoryRepositoryPort;
use Src\Modules\ServiceCategories\Domain\ValueObjects\ServiceCategoryId;

final class UpdateServiceCategoryHandler
{
    public function __construct(
        private readonly ServiceCategoryRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateServiceCategoryData $data): void
    {
        $id = ServiceCategoryId::fromString($uuid);
        $serviceCategory = $this->repository->find($id);

        if ($serviceCategory === null) {
            throw new RuntimeException('Service category not found.');
        }

        $serviceCategory->update(
            category: $data->category,
            type: $data->type,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($serviceCategory);
    }
}
