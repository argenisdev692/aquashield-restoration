<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * @OA\Schema(
 *     schema="BulkDeleteClaimData",
 *     required={"uuids"},
 *     @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
 * )
 */
#[MapInputName(SnakeCaseMapper::class)]
final class BulkDeleteClaimData extends Data
{
    public function __construct(
        /** @var string[] */
        public array $uuids,
    ) {}
}
