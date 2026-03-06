<?php

declare(strict_types=1);

namespace Modules\Users\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Modules\Users\Domain\Enums\UserStatus;

/**
 * @OA\Schema(
 *     schema="UpdateUserDTO",
 *     @OA\Property(property="name", type="string", nullable=true, example="John"),
 *     @OA\Property(property="last_name", type="string", nullable=true, example="Doe"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="john@example.com"),
 *     @OA\Property(property="username", type="string", nullable=true, example="johndoe"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="5551234"),
 *     @OA\Property(property="address", type="string", nullable=true, example="123 Main St"),
 *     @OA\Property(property="city", type="string", nullable=true, example="Miami"),
 *     @OA\Property(property="state", type="string", nullable=true, example="Florida"),
 *     @OA\Property(property="country", type="string", nullable=true, example="USA"),
 *     @OA\Property(property="zip_code", type="string", nullable=true, example="33101"),
 *     @OA\Property(property="status", type="string", nullable=true, example="active"),
 *     @OA\Property(property="role", type="string", nullable=true, example="user")
 * )
 */
#[MapInputName(SnakeCaseMapper::class)]
final class UpdateUserDTO extends Data
{
    public function __construct(
        #[MapInputName('name')]
        public ?string $name = null,
        #[MapInputName('last_name')]
        public ?string $lastName = null,
        #[MapInputName('email')]
        public ?string $email = null,
        #[MapInputName('username')]
        public ?string $username = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $zipCode = null,
        public ?UserStatus $status = null,
        public ?string $role = null,
    ) {
    }
}
