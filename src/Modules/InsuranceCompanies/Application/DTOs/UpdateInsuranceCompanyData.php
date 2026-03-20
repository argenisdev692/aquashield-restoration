<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * @OA\Schema(
 *     schema="UpdateInsuranceCompanyData",
 *     required={"insurance_company_name"},
 *     @OA\Property(property="insurance_company_name", type="string", example="Aqua Shield Insurance Updated"),
 *     @OA\Property(property="address", type="string", nullable=true, example="456 Updated St"),
 *     @OA\Property(property="address_2", type="string", nullable=true, example="Floor 3"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+1 (555) 555-0102"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="updated@example.com"),
 *     @OA\Property(property="website", type="string", nullable=true, example="https://updated.example.com")
 * )
 */
#[MapInputName(SnakeCaseMapper::class)]
final class UpdateInsuranceCompanyData extends Data
{
    public function __construct(
        public string $insuranceCompanyName,
        public ?string $address = null,
        public ?string $address2 = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $website = null,
    ) {}
}
