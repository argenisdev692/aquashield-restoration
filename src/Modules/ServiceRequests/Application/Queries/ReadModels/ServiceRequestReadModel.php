<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class ServiceRequestReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $requestedService,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}
}
