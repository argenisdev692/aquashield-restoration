<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\DTOs;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="UpdateFileEsxData",
 *     type="object",
 *     @OA\Property(property="file_name", type="string", nullable=true)
 * )
 */
final class UpdateFileEsxData extends Data
{
    public function __construct(
        public readonly ?string $fileName,
    ) {}
}
