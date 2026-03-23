<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class DocumentTemplateAdjusterData extends Data
{
    public function __construct(
        public string $uuid,
        public ?string $templateDescriptionAdjuster,
        public string $templateTypeAdjuster,
        public string $templatePathAdjuster,
        public int $publicAdjusterId,
        public ?string $publicAdjusterName,
        public int $uploadedBy,
        public ?string $uploadedByName,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
