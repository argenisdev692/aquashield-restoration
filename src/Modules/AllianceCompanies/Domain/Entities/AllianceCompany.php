<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Domain\Entities;

use InvalidArgumentException;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;
use Shared\Domain\Entities\AggregateRoot;

final class AllianceCompany extends AggregateRoot
{
    private function __construct(
        private AllianceCompanyId $id,
        private string $allianceCompanyName,
        private ?string $address,
        private ?string $phone,
        private ?string $email,
        private ?string $website,
        private int $userId,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->allianceCompanyName = self::normalizeRequiredString($allianceCompanyName, 'Alliance company name is required.');
        $this->address = self::normalizeNullableString($address);
        $this->phone = self::normalizeNullableString($phone);
        $this->email = self::normalizeNullableString($email);
        $this->website = self::normalizeNullableString($website);
        $this->userId = self::normalizeUserId($userId);
    }

    public static function create(
        AllianceCompanyId $id,
        string $allianceCompanyName,
        ?string $address,
        ?string $phone,
        ?string $email,
        ?string $website,
        int $userId,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            allianceCompanyName: $allianceCompanyName,
            address: $address,
            phone: $phone,
            email: $email,
            website: $website,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        AllianceCompanyId $id,
        string $allianceCompanyName,
        ?string $address,
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
            allianceCompanyName: $allianceCompanyName,
            address: $address,
            phone: $phone,
            email: $email,
            website: $website,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(
        string $allianceCompanyName,
        ?string $address,
        ?string $phone,
        ?string $email,
        ?string $website,
        string $updatedAt,
    ): void {
        $this->allianceCompanyName = self::normalizeRequiredString($allianceCompanyName, 'Alliance company name is required.');
        $this->address = self::normalizeNullableString($address);
        $this->phone = self::normalizeNullableString($phone);
        $this->email = self::normalizeNullableString($email);
        $this->website = self::normalizeNullableString($website);
        $this->updatedAt = $updatedAt;
    }

    public function id(): AllianceCompanyId
    {
        return $this->id;
    }

    public function allianceCompanyName(): string
    {
        return $this->allianceCompanyName;
    }

    public function address(): ?string
    {
        return $this->address;
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
        $normalized = trim($value);

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

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeUserId(int $userId): int
    {
        if ($userId < 1) {
            throw new InvalidArgumentException('Alliance company owner is required.');
        }

        return $userId;
    }
}
