<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\TypeDamages\Domain\ValueObjects\TypeDamageId;

class TypeDamage extends AggregateRoot
{
    private const ALLOWED_SEVERITIES = ['low', 'medium', 'high'];

    private function __construct(
        private TypeDamageId $id,
        private string $typeDamageName,
        private ?string $description,
        private string $severity,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->typeDamageName = self::normalizeName($typeDamageName);
        $this->description = self::normalizeDescription($description);
        $this->severity = self::normalizeSeverity($severity);
    }

    public static function create(
        TypeDamageId $id,
        string $typeDamageName,
        ?string $description,
        string $severity,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            typeDamageName: $typeDamageName,
            description: $description,
            severity: $severity,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        TypeDamageId $id,
        string $typeDamageName,
        ?string $description,
        string $severity,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            typeDamageName: $typeDamageName,
            description: $description,
            severity: $severity,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(string $typeDamageName, ?string $description, string $severity, string $updatedAt): void
    {
        $this->typeDamageName = self::normalizeName($typeDamageName);
        $this->description = self::normalizeDescription($description);
        $this->severity = self::normalizeSeverity($severity);
        $this->updatedAt = $updatedAt;
    }

    public function id(): TypeDamageId
    {
        return $this->id;
    }

    public function typeDamageName(): string
    {
        return $this->typeDamageName;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function severity(): string
    {
        return $this->severity;
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

    private static function normalizeName(string $typeDamageName): string
    {
        $normalized = trim($typeDamageName);

        if ($normalized === '') {
            throw new InvalidArgumentException('Type damage name is required.');
        }

        return $normalized;
    }

    private static function normalizeDescription(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $normalized = trim($description);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeSeverity(string $severity): string
    {
        $normalized = strtolower(trim($severity));

        if (!in_array($normalized, self::ALLOWED_SEVERITIES, true)) {
            throw new InvalidArgumentException('Invalid type damage severity.');
        }

        return $normalized;
    }
}
