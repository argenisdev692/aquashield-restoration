<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\ServiceCategories\Domain\ValueObjects\ServiceCategoryId;

class ServiceCategory extends AggregateRoot
{
    private function __construct(
        private ServiceCategoryId $id,
        private string $category,
        private ?string $type,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->category = self::normalizeCategory($category);
        $this->type = self::normalizeType($type);
    }

    public static function create(
        ServiceCategoryId $id,
        string $category,
        ?string $type,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            category: $category,
            type: $type,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        ServiceCategoryId $id,
        string $category,
        ?string $type,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            category: $category,
            type: $type,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(string $category, ?string $type, string $updatedAt): void
    {
        $this->category = self::normalizeCategory($category);
        $this->type = self::normalizeType($type);
        $this->updatedAt = $updatedAt;
    }

    public function id(): ServiceCategoryId
    {
        return $this->id;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function type(): ?string
    {
        return $this->type;
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

    private static function normalizeCategory(string $category): string
    {
        $normalized = $category |> trim(...);

        if ($normalized === '') {
            throw new InvalidArgumentException('Service category name is required.');
        }

        return $normalized;
    }

    private static function normalizeType(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $normalized = $type |> trim(...);

        return $normalized === '' ? null : $normalized;
    }
}
