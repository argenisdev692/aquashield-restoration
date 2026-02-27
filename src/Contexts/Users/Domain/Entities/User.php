<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Domain\Entities;

use Src\Contexts\Users\Domain\Enums\UserStatus;
use Src\Contexts\Users\Domain\ValueObjects\UserId;

use Src\Core\Shared\Domain\Entities\AggregateRoot;

/**
 * User — Domain Entity (Aggregate Root)
 *
 * Represents the admin-managed User in the Users bounded context.
 * Agnostic of Eloquent / infrastructure.
 */
final readonly class User extends AggregateRoot
{
    public function __construct(
        #[\Override]
        public UserId $id,
        public string $uuid,
        public string $name,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $username = null,
        public ?string $phone = null,
        public ?string $profilePhotoPath = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $zipCode = null,
        public UserStatus $status = UserStatus::Active,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * @return string
     */
    #[\NoDiscard]
    public function fullName(): string
    {
        return trim("{$this->name} {$this->lastName}");
    }
}
