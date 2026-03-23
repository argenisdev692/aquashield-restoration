<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\DTOs;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="StoreFileEsxData",
 *     type="object",
 *     required={"file"},
 *     @OA\Property(property="file_name", type="string", nullable=true),
 *     @OA\Property(property="uploaded_by", type="integer")
 * )
 */
final class StoreFileEsxData extends Data
{
    public function __construct(
        public readonly ?string $fileName,
        public readonly int     $uploadedBy,
    ) {}
}
