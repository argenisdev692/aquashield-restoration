<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\DTOs;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="BulkDeleteFileEsxData",
 *     type="object",
 *     required={"uuids"},
 *     @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
 * )
 */
final class BulkDeleteFileEsxData extends Data
{
    public function __construct(
        /** @var string[] */
        public readonly array $uuids,
    ) {}
}
