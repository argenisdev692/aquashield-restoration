<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class ProjectTypeListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $title,
        public ?string $description,
        public string $status,
        public string $serviceCategoryUuid,
        public ?string $serviceCategoryName,
        public string $createdAt,
        public ?string $deletedAt = null,
    ) {}
}
