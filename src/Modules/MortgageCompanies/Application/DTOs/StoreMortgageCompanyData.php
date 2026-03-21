<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * @OA\Schema(
 *     schema="StoreMortgageCompanyData",
 *     required={"mortgage_company_name"},
 *     @OA\Property(property="mortgage_company_name", type="string", example="AquaShield Mortgage"),
 *     @OA\Property(property="address", type="string", nullable=true, example="123 Main St"),
 *     @OA\Property(property="address_2", type="string", nullable=true, example="Suite 200"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+1 (555) 555-0101"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="info@mortgage.com"),
 *     @OA\Property(property="website", type="string", nullable=true, example="https://mortgage.com")
 * )
 */
#[MapInputName(SnakeCaseMapper::class)]
final class StoreMortgageCompanyData extends Data
{
    public function __construct(
        public string $mortgageCompanyName,
        public ?string $address,
        public ?string $address2,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public int $userId,
    ) {}
}
