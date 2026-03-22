<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class DocumentTemplateAllianceData extends Data
{
    public function __construct(
        public string $uuid,
        public string $templateNameAlliance,
        public ?string $templateDescriptionAlliance,
        public string $templateTypeAlliance,
        public string $templatePathAlliance,
        public int $allianceCompanyId,
        public ?string $allianceCompanyName,
        public int $uploadedBy,
        public ?string $uploadedByName,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
