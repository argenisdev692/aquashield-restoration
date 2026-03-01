<?php

declare(strict_types=1);

namespace Src\Modules\Products\Domain\Entities;

use Src\Modules\Products\Domain\ValueObjects\ProductId;
use Src\Shared\Domain\AggregateRoot;

class Product extends AggregateRoot
{
    private function __construct(
        private ProductId $id,
        private string $categoryId,
        private string $name,
        private string $description,
        private float $price,
        private string $unit,
        private int $orderPosition,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null
    ) {}

    public static function create(
        ProductId $id,
        string $categoryId,
        string $name,
        string $description,
        float $price,
        string $unit,
        int $orderPosition,
        string $createdAt
    ): self {
        return new self(
            id: $id,
            categoryId: $categoryId,
            name: $name,
            description: $description,
            price: $price,
            unit: $unit,
            orderPosition: $orderPosition,
            createdAt: $createdAt,
            updatedAt: $createdAt
        );
    }

    public function update(
        string $categoryId,
        string $name,
        string $description,
        float $price,
        string $unit,
        int $orderPosition,
        string $updatedAt
    ): void {
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->unit = $unit;
        $this->orderPosition = $orderPosition;
        $this->updatedAt = $updatedAt;
    }

    public function id(): ProductId
    {
        return $this->id;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function orderPosition(): int
    {
        return $this->orderPosition;
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
}
