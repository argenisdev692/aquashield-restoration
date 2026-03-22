<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\ClaimStatuses\Domain\ValueObjects\ClaimStatusId;

class ClaimStatus extends AggregateRoot
{
    private function __construct(
        private ClaimStatusId $id,
        private string $claimStatusName,
        private ?string $backgroundColor,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->claimStatusName = self::normalizeName($claimStatusName);
        $this->backgroundColor = self::normalizeColor($backgroundColor);
    }

    public static function create(
        ClaimStatusId $id,
        string $claimStatusName,
        ?string $backgroundColor,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            claimStatusName: $claimStatusName,
            backgroundColor: $backgroundColor,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        ClaimStatusId $id,
        string $claimStatusName,
        ?string $backgroundColor,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            claimStatusName: $claimStatusName,
            backgroundColor: $backgroundColor,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(string $claimStatusName, ?string $backgroundColor, string $updatedAt): void
    {
        $this->claimStatusName = self::normalizeName($claimStatusName);
        $this->backgroundColor = self::normalizeColor($backgroundColor);
        $this->updatedAt = $updatedAt;
    }

    public function id(): ClaimStatusId
    {
        return $this->id;
    }

    public function claimStatusName(): string
    {
        return $this->claimStatusName;
    }

    public function backgroundColor(): ?string
    {
        return $this->backgroundColor;
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

    private static function normalizeName(string $claimStatusName): string
    {
        $normalized = trim($claimStatusName);

        if ($normalized === '') {
            throw new InvalidArgumentException('Claim status name is required.');
        }

        return $normalized;
    }

    private static function normalizeColor(?string $backgroundColor): ?string
    {
        if ($backgroundColor === null) {
            return null;
        }

        $normalized = trim($backgroundColor);

        if ($normalized === '') {
            return null;
        }

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $normalized)) {
            throw new InvalidArgumentException('Background color must be a valid hex color (e.g. #FF5733).');
        }

        return strtoupper($normalized);
    }
}
