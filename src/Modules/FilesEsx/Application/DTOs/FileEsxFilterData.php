<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\DTOs;

use OpenApi\Attributes as OA;
use Spatie\LaravelData\Data;

#[OA\Schema(
    schema: 'FileEsxFilterData',
    description: 'Filter parameters for listing Files ESX',
    properties: [
        new OA\Property(property: 'search', type: 'string', nullable: true),
        new OA\Property(property: 'uploaded_by', type: 'integer', nullable: true),
        new OA\Property(property: 'date_from', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'date_to', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'page', type: 'integer', default: 1),
        new OA\Property(property: 'per_page', type: 'integer', default: 15),
    ],
)]
final class FileEsxFilterData extends Data
{
    public function __construct(
        public readonly ?string $search     = null,
        public readonly ?int    $uploadedBy = null,
        public readonly ?string $dateFrom   = null,
        public readonly ?string $dateTo     = null,
        public readonly int     $page       = 1,
        public readonly int     $perPage    = 15,
    ) {}
}
