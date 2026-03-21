<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Domain\Entities;

use InvalidArgumentException;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use Shared\Domain\Entities\AggregateRoot;

final class PublicCompany extends AggregateRoot
{
    private function __construct(
        private PublicCompanyId $id,
        private string $publicCompanyName,
        private ?string $address,
        private ?string $address2,
        private ?string $phone,
        private ?string $email,
        private ?string $website,
        private ?string $unit,
        private int $userId,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->publicCompanyName = self::normalizeRequired($publicCompanyName, 'Public company name is required.');
        $this->address = self::normalizeOptional($address);
        $this->address2 = self::normalizeOptional($address2);
        $this->phone = self::normalizeOptional($phone);
        $this->email = self::normalizeOptional($email);
        $this->website = self::normalizeOptional($website);
        $this->unit = self::normalizeOptional($unit);

        if ($this->userId <= 0) {
            throw new InvalidArgumentException('User is required.');
        }
    }

    public static function create(
        PublicCompanyId $id,
        string $publicCompanyName,
        ?string $address,
        ?string $address2,
        ?string $phone,
        ?string $email,
        ?string $website,
        ?string $unit,
        int $userId,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            publicCompanyName: $publicCompanyName,
            address: $address,
            address2: $address2,
            phone: $phone,
            email: $email,
            website: $website,
            unit: $unit,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        PublicCompanyId $id,
        string $publicCompanyName,
        ?string $address,
        ?string $address2,
        ?string $phone,
        ?string $email,
        ?string $website,
        ?string $unit,
        int $userId,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            publicCompanyName: $publicCompanyName,
            address: $address,
            address2: $address2,
            phone: $phone,
            email: $email,
            website: $website,
            unit: $unit,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(
        string $publicCompanyName,
        ?string $address,
        ?string $address2,
        ?string $phone,
        ?string $email,
        ?string $website,
        ?string $unit,
        string $updatedAt,
    ): void {
        $this->publicCompanyName = self::normalizeRequired($publicCompanyName, 'Public company name is required.');
        $this->address = self::normalizeOptional($address);
        $this->address2 = self::normalizeOptional($address2);
        $this->phone = self::normalizeOptional($phone);
        $this->email = self::normalizeOptional($email);
        $this->website = self::normalizeOptional($website);
        $this->unit = self::normalizeOptional($unit);
        $this->updatedAt = $updatedAt;
    }

    public function id(): PublicCompanyId
    {
        return $this->id;
    }

    public function publicCompanyName(): string
    {
        return $this->publicCompanyName;
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

    public function unit(): ?string
    {
        return $this->unit;
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

    private static function normalizeRequired(string $value, string $message): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidArgumentException($message);
        }

        return $normalized;
    }

    private static function normalizeOptional(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
