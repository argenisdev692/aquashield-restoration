<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\ProjectTypes\Domain\ValueObjects\ProjectTypeId;

class ProjectType extends AggregateRoot
{
    private const ALLOWED_STATUSES = ['active', 'inactive'];

    private function __construct(
        private ProjectTypeId $id,
        private string $title,
        private ?string $description,
        private string $status,
        private string $serviceCategoryUuid,
        private string $createdAt,
        private string $updatedAt,
        private ?string $deletedAt = null,
    ) {
        $this->title  = self::normalizeTitle($title);
        $this->status = self::normalizeStatus($status);
    }

    public static function create(
        ProjectTypeId $id,
        string $title,
        ?string $description,
        string $status,
        string $serviceCategoryUuid,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            title: $title,
            description: $description,
            status: $status,
            serviceCategoryUuid: $serviceCategoryUuid,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        ProjectTypeId $id,
        string $title,
        ?string $description,
        string $status,
        string $serviceCategoryUuid,
        string $createdAt,
        string $updatedAt,
        ?string $deletedAt,
    ): self {
        return new self(
            id: $id,
            title: $title,
            description: $description,
            status: $status,
            serviceCategoryUuid: $serviceCategoryUuid,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            deletedAt: $deletedAt,
        );
    }

    public function update(string $title, ?string $description, string $status, string $serviceCategoryUuid, string $updatedAt): void
    {
        $this->title              = self::normalizeTitle($title);
        $this->description        = $description;
        $this->status             = self::normalizeStatus($status);
        $this->serviceCategoryUuid = $serviceCategoryUuid;
        $this->updatedAt          = $updatedAt;
    }

    public function id(): ProjectTypeId
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function serviceCategoryUuid(): string
    {
        return $this->serviceCategoryUuid;
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

    private static function normalizeTitle(string $title): string
    {
        $normalized = trim($title);

        if ($normalized === '') {
            throw new InvalidArgumentException('Project type title is required.');
        }

        return $normalized;
    }

    private static function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        if (!in_array($normalized, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException('Invalid project type status.');
        }

        return $normalized;
    }
}
