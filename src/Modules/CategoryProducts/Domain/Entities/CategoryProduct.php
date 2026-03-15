<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\CategoryProducts\Domain\ValueObjects\CategoryProductId;

class CategoryProduct extends AggregateRoot
{
    private function __construct(
        private CategoryProductId $id,
        private string $categoryProductName,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->categoryProductName = self::normalizeName($categoryProductName);
    }

    public static function create(CategoryProductId $id, string $categoryProductName, string $createdAt): self
    {
        return new self(
            id: $id,
            categoryProductName: $categoryProductName,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        CategoryProductId $id,
        string $categoryProductName,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            categoryProductName: $categoryProductName,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(string $categoryProductName, string $updatedAt): void
    {
        $this->categoryProductName = self::normalizeName($categoryProductName);
        $this->updatedAt = $updatedAt;
    }

    public function id(): CategoryProductId
    {
        return $this->id;
    }

    public function categoryProductName(): string
    {
        return $this->categoryProductName;
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

    private static function normalizeName(string $categoryProductName): string
    {
        $normalized = trim($categoryProductName);

        if ($normalized === '') {
            throw new InvalidArgumentException('Category product name is required.');
        }

        return $normalized;
    }
}
