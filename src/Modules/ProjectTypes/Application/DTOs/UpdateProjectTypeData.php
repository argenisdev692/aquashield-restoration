<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateProjectTypeData extends Data
{
    public function __construct(
        public string $title,
        public string $serviceCategoryUuid,
        public ?string $description = null,
        public string $status = 'active',
    ) {}
}
