<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Domain\Entities;

use InvalidArgumentException;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;
use Shared\Domain\Entities\AggregateRoot;

final class MortgageCompany extends AggregateRoot
{
    private function __construct(
        private MortgageCompanyId $id,
        private string $mortgageCompanyName,
        private ?string $address,
        private ?string $address2,
        private ?string $phone,
        private ?string $email,
        private ?string $website,
        private int $userId,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->mortgageCompanyName = self::normalizeRequiredString($mortgageCompanyName, 'Mortgage company name is required.');
        $this->address = self::normalizeNullableString($address);
        $this->address2 = self::normalizeNullableString($address2);
        $this->phone = self::normalizeNullableString($phone);
        $this->email = self::normalizeNullableString($email);
        $this->website = self::normalizeNullableString($website);
        $this->userId = self::normalizeUserId($userId);
    }

    public static function create(
        MortgageCompanyId $id,
        string $mortgageCompanyName,
        ?string $address,
        ?string $address2,
        ?string $phone,
        ?string $email,
        ?string $website,
        int $userId,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            mortgageCompanyName: $mortgageCompanyName,
            address: $address,
            address2: $address2,
            phone: $phone,
            email: $email,
            website: $website,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        MortgageCompanyId $id,
        string $mortgageCompanyName,
        ?string $address,
        ?string $address2,
        ?string $phone,
        ?string $email,
        ?string $website,
        int $userId,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            mortgageCompanyName: $mortgageCompanyName,
            address: $address,
            address2: $address2,
            phone: $phone,
            email: $email,
            website: $website,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    #[\NoDiscard('Updated entity must be captured and persisted')]
    public function update(
        string $mortgageCompanyName,
        ?string $address,
        ?string $address2,
        ?string $phone,
        ?string $email,
        ?string $website,
        string $updatedAt,
    ): self {
        return clone($this, [
            'mortgageCompanyName' => self::normalizeRequiredString($mortgageCompanyName, 'Mortgage company name is required.'),
            'address'             => self::normalizeNullableString($address),
            'address2'            => self::normalizeNullableString($address2),
            'phone'               => self::normalizeNullableString($phone),
            'email'               => self::normalizeNullableString($email),
            'website'             => self::normalizeNullableString($website),
            'updatedAt'           => $updatedAt,
        ]);
    }

    public function id(): MortgageCompanyId
    {
        return $this->id;
    }

    public function mortgageCompanyName(): string
    {
        return $this->mortgageCompanyName;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function address2(): ?string
    {
        return $this->address2;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function website(): ?string
    {
        return $this->website;
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

    private static function normalizeRequiredString(string $value, string $message): string
    {
        $normalized = $value |> trim(...);

        if ($normalized === '') {
            throw new InvalidArgumentException($message);
        }

        return $normalized;
    }

    private static function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = $value |> trim(...);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeUserId(int $userId): int
    {
        if ($userId < 1) {
            throw new InvalidArgumentException('Mortgage company owner is required.');
        }

        return $userId;
    }
}
