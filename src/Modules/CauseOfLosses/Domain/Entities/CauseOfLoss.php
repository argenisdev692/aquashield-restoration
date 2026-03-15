<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\CauseOfLosses\Domain\ValueObjects\CauseOfLossId;

class CauseOfLoss extends AggregateRoot
{
    private const ALLOWED_SEVERITIES = ['low', 'medium', 'high'];

    private function __construct(
        private CauseOfLossId $id,
        private string $causeLossName,
        private ?string $description,
        private string $severity,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->causeLossName = self::normalizeName($causeLossName);
        $this->description = self::normalizeDescription($description);
        $this->severity = self::normalizeSeverity($severity);
    }

    public static function create(
        CauseOfLossId $id,
        string $causeLossName,
        ?string $description,
        string $severity,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            causeLossName: $causeLossName,
            description: $description,
            severity: $severity,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        CauseOfLossId $id,
        string $causeLossName,
        ?string $description,
        string $severity,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            causeLossName: $causeLossName,
            description: $description,
            severity: $severity,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(string $causeLossName, ?string $description, string $severity, string $updatedAt): void
    {
        $this->causeLossName = self::normalizeName($causeLossName);
        $this->description = self::normalizeDescription($description);
        $this->severity = self::normalizeSeverity($severity);
        $this->updatedAt = $updatedAt;
    }

    public function id(): CauseOfLossId
    {
        return $this->id;
    }

    public function causeLossName(): string
    {
        return $this->causeLossName;
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

    private static function normalizeName(string $causeLossName): string
    {
        $normalized = trim($causeLossName);

        if ($normalized === '') {
            throw new InvalidArgumentException('Cause of loss name is required.');
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
            throw new InvalidArgumentException('Invalid cause of loss severity.');
        }

        return $normalized;
    }
}
