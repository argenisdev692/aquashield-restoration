<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\DTOs;

/**
 * UpdateUserDTO — Data Transfer Object for user updates.
 *
 * 🧬 readonly (PHP 8.5)
 */
final readonly class UpdateUserDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $username = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $zipCode = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'username' => $this->username,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip_code' => $this->zipCode,
        ], fn(mixed $v): bool => $v !== null);
    }
}
