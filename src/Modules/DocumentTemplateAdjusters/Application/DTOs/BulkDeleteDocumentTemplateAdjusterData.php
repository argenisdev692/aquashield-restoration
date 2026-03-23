<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class BulkDeleteDocumentTemplateAdjusterData extends Data
{
    public function __construct(
        /** @var string[] */
        public array $uuids,
    ) {}
}
