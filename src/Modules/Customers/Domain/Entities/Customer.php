<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\Customers\Domain\ValueObjects\CustomerId;

class Customer extends AggregateRoot
{
    private function __construct(
        private CustomerId $id,
        private string $name,
        private ?string $lastName,
        private string $email,
        private ?string $cellPhone,
        private ?string $homePhone,
        private ?string $occupation,
        private int $userId,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->name = self::normalizeName($name);
        $this->email = self::normalizeEmail($email);
    }

    public static function create(
        CustomerId $id,
        string $name,
        ?string $lastName,
        string $email,
        ?string $cellPhone,
        ?string $homePhone,
        ?string $occupation,
        int $userId,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            lastName: $lastName,
            email: $email,
            cellPhone: $cellPhone,
            homePhone: $homePhone,
            occupation: $occupation,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        CustomerId $id,
        string $name,
        ?string $lastName,
        string $email,
        ?string $cellPhone,
        ?string $homePhone,
        ?string $occupation,
        int $userId,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            lastName: $lastName,
            email: $email,
            cellPhone: $cellPhone,
            homePhone: $homePhone,
            occupation: $occupation,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(
        string $name,
        ?string $lastName,
        string $email,
        ?string $cellPhone,
        ?string $homePhone,
        ?string $occupation,
        int $userId,
        string $updatedAt,
    ): void {
        $this->name = self::normalizeName($name);
        $this->lastName = $lastName;
        $this->email = self::normalizeEmail($email);
        $this->cellPhone = $cellPhone;
        $this->homePhone = $homePhone;
        $this->occupation = $occupation;
        $this->userId = $userId;
        $this->updatedAt = $updatedAt;
    }

    public function id(): CustomerId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function lastName(): ?string
    {
        return $this->lastName;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function cellPhone(): ?string
    {
        return $this->cellPhone;
    }

    public function homePhone(): ?string
    {
        return $this->homePhone;
    }

    public function occupation(): ?string
    {
        return $this->occupation;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function updatedAt(): string
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?string
    {
        return $this->deletedAt;
    }

    private static function normalizeName(string $name): string
    {
        $normalized = $name |> trim(...);

        if ($normalized === '') {
            throw new InvalidArgumentException('Customer name is required.');
        }

        return $normalized;
    }

    private static function normalizeEmail(string $email): string
    {
        try {
            return filter_var($email |> trim(...), FILTER_VALIDATE_EMAIL, FILTER_THROW_ON_FAILURE);
        } catch (\ValueError $e) {
            throw new InvalidArgumentException('Invalid customer email address.', previous: $e);
        }
    }
}
