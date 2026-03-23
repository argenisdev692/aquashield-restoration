<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class DocumentTemplateData extends Data
{
    public function __construct(
        public string $uuid,
        public string $templateName,
        public ?string $templateDescription,
        public string $templateType,
        public string $templatePath,
        public int $uploadedBy,
        public ?string $uploadedByName,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
