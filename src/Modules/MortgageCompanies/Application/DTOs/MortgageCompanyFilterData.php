<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * @OA\Schema(
 *     schema="MortgageCompanyFilterData",
 *     @OA\Property(property="search", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", nullable=true, enum={"active", "deleted"}),
 *     @OA\Property(property="date_from", type="string", format="date", nullable=true),
 *     @OA\Property(property="date_to", type="string", format="date", nullable=true),
 *     @OA\Property(property="page", type="integer", default=1),
 *     @OA\Property(property="per_page", type="integer", default=15)
 * )
 */
#[MapInputName(SnakeCaseMapper::class)]
final class MortgageCompanyFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}
