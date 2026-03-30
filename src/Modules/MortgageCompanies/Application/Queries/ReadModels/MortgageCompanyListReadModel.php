<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * @OA\Schema(
 *     schema="MortgageCompanyListReadModel",
 *     @OA\Property(property="company_id", type="integer"),
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="mortgage_company_name", type="string"),
 *     @OA\Property(property="address", type="string", nullable=true),
 *     @OA\Property(property="address_2", type="string", nullable=true),
 *     @OA\Property(property="phone", type="string", nullable=true),
 *     @OA\Property(property="email", type="string", format="email", nullable=true),
 *     @OA\Property(property="website", type="string", nullable=true),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
#[MapOutputName(SnakeCaseMapper::class)]
final class MortgageCompanyListReadModel extends Data
{
    public function __construct(
        public int $companyId,
        public string $uuid,
        public string $mortgageCompanyName,
        public ?string $address,
        public ?string $address2,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public int $userId,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}
}
