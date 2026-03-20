<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * @OA\Schema(
 *     schema="InsuranceCompanyReadModel",
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="insurance_company_name", type="string"),
 *     @OA\Property(property="address", type="string", nullable=true),
 *     @OA\Property(property="address_2", type="string", nullable=true),
 *     @OA\Property(property="phone", type="string", nullable=true),
 *     @OA\Property(property="email", type="string", format="email", nullable=true),
 *     @OA\Property(property="website", type="string", nullable=true),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", nullable=true),
 *     @OA\Property(property="updated_at", type="string", nullable=true),
 *     @OA\Property(property="deleted_at", type="string", nullable=true)
 * )
 */
#[MapOutputName(SnakeCaseMapper::class)]
final class InsuranceCompanyReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $insuranceCompanyName,
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
