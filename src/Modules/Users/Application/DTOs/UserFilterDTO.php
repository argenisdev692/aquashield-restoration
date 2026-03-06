<?php

declare(strict_types=1);

namespace Modules\Users\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Modules\Users\Domain\Enums\UserStatus;

/**
 * @OA\Schema(
 *     schema="UserFilterDTO",
 *     @OA\Property(property="search", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", nullable=true),
 *     @OA\Property(property="role", type="string", nullable=true),
 *     @OA\Property(property="date_from", type="string", format="date", nullable=true),
 *     @OA\Property(property="date_to", type="string", format="date", nullable=true),
 *     @OA\Property(property="sort_by", type="string", nullable=true, example="created_at"),
 *     @OA\Property(property="sort_dir", type="string", nullable=true, example="desc"),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="per_page", type="integer", example=15)
 * )
 */
#[MapInputName(SnakeCaseMapper::class)]
final class UserFilterDTO extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?UserStatus $status = null,
        public ?string $role = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?string $sortBy = 'created_at',
        public ?string $sortDir = 'desc',
        public int $page = 1,
        public int $perPage = 15,
    ) {
    }
}
