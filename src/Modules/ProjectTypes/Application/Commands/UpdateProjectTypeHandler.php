<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Application\Commands;

use RuntimeException;
use Src\Modules\ProjectTypes\Application\DTOs\UpdateProjectTypeData;
use Src\Modules\ProjectTypes\Domain\Ports\ProjectTypeRepositoryPort;
use Src\Modules\ProjectTypes\Domain\ValueObjects\ProjectTypeId;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class UpdateProjectTypeHandler
{
    public function __construct(
        private readonly ProjectTypeRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateProjectTypeData $data): void
    {
        $id          = ProjectTypeId::fromString($uuid);
        $projectType = $this->repository->find($id);

        if ($projectType === null) {
            throw new RuntimeException('Project type not found.');
        }

        $serviceCategory = ServiceCategoryEloquentModel::where('uuid', $data->serviceCategoryUuid)->first();

        if ($serviceCategory === null) {
            throw new RuntimeException('Service category not found.');
        }

        $projectType->update(
            title: $data->title,
            description: $data->description,
            status: $data->status,
            serviceCategoryUuid: $data->serviceCategoryUuid,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($projectType);
    }
}
