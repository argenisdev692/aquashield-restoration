<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\Portfolios\Domain\ValueObjects\PortfolioId;

class Portfolio extends AggregateRoot
{
    private function __construct(
        private PortfolioId $id,
        private ?string $projectTypeUuid,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {}

    public static function create(
        PortfolioId $id,
        ?string $projectTypeUuid,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            projectTypeUuid: $projectTypeUuid,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        PortfolioId $id,
        ?string $projectTypeUuid,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            projectTypeUuid: $projectTypeUuid,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(?string $projectTypeUuid, string $updatedAt): void
    {
        $this->projectTypeUuid = $projectTypeUuid;
        $this->updatedAt       = $updatedAt;
    }

    public function id(): PortfolioId
    {
        return $this->id;
    }

    public function projectTypeUuid(): ?string
    {
        return $this->projectTypeUuid;
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

    public function assertProjectTypeIsProvided(): void
    {
        if ($this->projectTypeUuid === null) {
            throw new InvalidArgumentException('Portfolio must belong to a project type.');
        }

        $normalized = $this->projectTypeUuid |> trim(...);

        if ($normalized === '') {
            throw new InvalidArgumentException('Portfolio must belong to a project type.');
        }
    }
}
