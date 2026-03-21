<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Application\Commands;

use RuntimeException;
use Src\Modules\ProjectTypes\Application\DTOs\StoreProjectTypeData;
use Src\Modules\ProjectTypes\Domain\Entities\ProjectType;
use Src\Modules\ProjectTypes\Domain\Ports\ProjectTypeRepositoryPort;
use Src\Modules\ProjectTypes\Domain\ValueObjects\ProjectTypeId;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class CreateProjectTypeHandler
{
    public function __construct(
        private readonly ProjectTypeRepositoryPort $repository,
    ) {}

    public function handle(StoreProjectTypeData $data): string
    {
        $serviceCategory = ServiceCategoryEloquentModel::where('uuid', $data->serviceCategoryUuid)->first();

        if ($serviceCategory === null) {
            throw new RuntimeException('Service category not found.');
        }

        $id          = ProjectTypeId::generate();
        $projectType = ProjectType::create(
            id: $id,
            title: $data->title,
            description: $data->description,
            status: $data->status,
            serviceCategoryUuid: $data->serviceCategoryUuid,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($projectType);

        return $id->toString();
    }
}
