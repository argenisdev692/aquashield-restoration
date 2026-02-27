<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\DTOs;

/**
 * CreateUserDTO — Data Transfer Object for user creation.
 *
 * 🧬 readonly (PHP 8.5)
 */
final readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $lastName = null,
        public ?string $username = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $zipCode = null,
        public ?string $password = null,
    ) {
    }
}
