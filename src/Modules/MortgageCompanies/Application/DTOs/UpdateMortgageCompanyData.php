<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * @OA\Schema(
 *     schema="UpdateMortgageCompanyData",
 *     required={"mortgage_company_name"},
 *     @OA\Property(property="mortgage_company_name", type="string", example="AquaShield Mortgage Updated"),
 *     @OA\Property(property="address", type="string", nullable=true, example="456 Updated St"),
 *     @OA\Property(property="address_2", type="string", nullable=true, example="Floor 3"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+1 (555) 555-0102"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="updated@mortgage.com"),
 *     @OA\Property(property="website", type="string", nullable=true, example="https://updated.mortgage.com")
 * )
 */
#[MapInputName(SnakeCaseMapper::class)]
final class UpdateMortgageCompanyData extends Data
{
    public function __construct(
        public string $mortgageCompanyName,
        public ?string $address,
        public ?string $address2,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
    ) {}
}
