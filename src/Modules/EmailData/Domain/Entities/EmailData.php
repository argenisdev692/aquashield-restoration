<?php

declare(strict_types=1);

namespace Modules\EmailData\Domain\Entities;

use InvalidArgumentException;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;
use Shared\Domain\Entities\AggregateRoot;

final class EmailData extends AggregateRoot
{
    private function __construct(
        private EmailDataId $id,
        private ?string $description,
        private string $email,
        private ?string $phone,
        private ?string $type,
        private int $userId,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->description = self::normalizeDescription($description);
        $this->email = self::normalizeEmail($email);
        $this->phone = self::normalizePhone($phone);
        $this->type = self::normalizeType($type);
        $this->userId = self::normalizeUserId($userId);
    }

    public static function create(
        EmailDataId $id,
        ?string $description,
        string $email,
        ?string $phone,
        ?string $type,
        int $userId,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            description: $description,
            email: $email,
            phone: $phone,
            type: $type,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        EmailDataId $id,
        ?string $description,
        string $email,
        ?string $phone,
        ?string $type,
        int $userId,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            description: $description,
            email: $email,
            phone: $phone,
            type: $type,
            userId: $userId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(?string $description, string $email, ?string $phone, ?string $type, string $updatedAt): void
    {
        $this->description = self::normalizeDescription($description);
        $this->email = self::normalizeEmail($email);
        $this->phone = self::normalizePhone($phone);
        $this->type = self::normalizeType($type);
        $this->updatedAt = $updatedAt;
    }

    public function id(): EmailDataId
    {
        return $this->id;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function type(): ?string
    {
        return $this->type;
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

    private static function normalizeDescription(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $normalized = trim($description);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeEmail(string $email): string
    {
        $normalized = strtolower(trim($email));

        if ($normalized === '') {
            throw new InvalidArgumentException('Email is required.');
        }

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Email is invalid.');
        }

        return $normalized;
    }

    private static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $normalized = trim($phone);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeType(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $normalized = trim($type);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeUserId(int $userId): int
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('User is required.');
        }

        return $userId;
    }
}
