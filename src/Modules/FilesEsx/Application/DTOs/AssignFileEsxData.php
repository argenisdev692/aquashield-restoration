<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\DTOs;

use Spatie\LaravelData\Data;

/**
 * @OA\Schema(
 *     schema="AssignFileEsxData",
 *     type="object",
 *     required={"public_adjuster_id"},
 *     @OA\Property(property="public_adjuster_id", type="integer")
 * )
 */
final class AssignFileEsxData extends Data
{
    public function __construct(
        public readonly int $publicAdjusterId,
        public readonly int $assignedBy,
    ) {}
}
