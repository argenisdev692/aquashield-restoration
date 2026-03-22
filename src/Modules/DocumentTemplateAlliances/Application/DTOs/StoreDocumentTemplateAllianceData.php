<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class StoreDocumentTemplateAllianceData extends Data
{
    public function __construct(
        public string $templateNameAlliance,
        public ?string $templateDescriptionAlliance,
        public string $templateTypeAlliance,
        public int $allianceCompanyId,
        public int $uploadedBy,
    ) {}
}
