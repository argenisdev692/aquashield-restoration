<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\DocumentTemplateAdjusters\Domain\ValueObjects\DocumentTemplateAdjusterId;

final class DocumentTemplateAdjuster extends AggregateRoot
{
    private DocumentTemplateAdjusterId $id;

    private ?string $templateDescriptionAdjuster {
        get => $this->templateDescriptionAdjuster;
        set(?string $value) => $this->templateDescriptionAdjuster = self::normalizeNullable($value);
    }

    private string $templateTypeAdjuster {
        get => $this->templateTypeAdjuster;
        set(string $value) => $this->templateTypeAdjuster = self::normalizeRequired($value, 'Template type is required.');
    }

    private string $templatePathAdjuster {
        get => $this->templatePathAdjuster;
        set(string $value) => $this->templatePathAdjuster = self::normalizeRequired($value, 'Template path is required.');
    }

    private int $publicAdjusterId {
        get => $this->publicAdjusterId;
        set(int $value) => $this->publicAdjusterId = self::normalizePositiveInt($value, 'Public adjuster user is required.');
    }

    private int $uploadedBy {
        get => $this->uploadedBy;
        set(int $value) => $this->uploadedBy = self::normalizePositiveInt($value, 'Uploaded by user is required.');
    }

    private string $createdAt;
    private string $updatedAt;

    private function __construct(
        DocumentTemplateAdjusterId $id,
        ?string $templateDescriptionAdjuster,
        string $templateTypeAdjuster,
        string $templatePathAdjuster,
        int $publicAdjusterId,
        int $uploadedBy,
        string $createdAt,
        string $updatedAt,
    ) {
        $this->id                           = $id;
        $this->templateDescriptionAdjuster  = $templateDescriptionAdjuster;
        $this->templateTypeAdjuster         = $templateTypeAdjuster;
        $this->templatePathAdjuster         = $templatePathAdjuster;
        $this->publicAdjusterId             = $publicAdjusterId;
        $this->uploadedBy                   = $uploadedBy;
        $this->createdAt                    = $createdAt;
        $this->updatedAt                    = $updatedAt;
    }

    public static function create(
        DocumentTemplateAdjusterId $id,
        ?string $templateDescriptionAdjuster,
        string $templateTypeAdjuster,
        string $templatePathAdjuster,
        int $publicAdjusterId,
        int $uploadedBy,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            templateDescriptionAdjuster: $templateDescriptionAdjuster,
            templateTypeAdjuster: $templateTypeAdjuster,
            templatePathAdjuster: $templatePathAdjuster,
            publicAdjusterId: $publicAdjusterId,
            uploadedBy: $uploadedBy,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        DocumentTemplateAdjusterId $id,
        ?string $templateDescriptionAdjuster,
        string $templateTypeAdjuster,
        string $templatePathAdjuster,
        int $publicAdjusterId,
        int $uploadedBy,
        string $createdAt,
        string $updatedAt,
    ): self {
        return new self(
            id: $id,
            templateDescriptionAdjuster: $templateDescriptionAdjuster,
            templateTypeAdjuster: $templateTypeAdjuster,
            templatePathAdjuster: $templatePathAdjuster,
            publicAdjusterId: $publicAdjusterId,
            uploadedBy: $uploadedBy,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function update(
        ?string $templateDescriptionAdjuster,
        string $templateTypeAdjuster,
        string $templatePathAdjuster,
        int $publicAdjusterId,
        string $updatedAt,
    ): void {
        $this->templateDescriptionAdjuster = $templateDescriptionAdjuster;
        $this->templateTypeAdjuster        = $templateTypeAdjuster;
        $this->templatePathAdjuster        = $templatePathAdjuster;
        $this->publicAdjusterId            = $publicAdjusterId;
        $this->updatedAt                   = $updatedAt;
    }

    public function id(): DocumentTemplateAdjusterId { return $this->id; }
    public function templateDescriptionAdjuster(): ?string { return $this->templateDescriptionAdjuster; }
    public function templateTypeAdjuster(): string { return $this->templateTypeAdjuster; }
    public function templatePathAdjuster(): string { return $this->templatePathAdjuster; }
    public function publicAdjusterId(): int { return $this->publicAdjusterId; }
    public function uploadedBy(): int { return $this->uploadedBy; }
    public function createdAt(): string { return $this->createdAt; }
    public function updatedAt(): string { return $this->updatedAt; }

    private static function normalizeRequired(string $value, string $message): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidArgumentException($message);
        }

        return $normalized;
    }

    private static function normalizeNullable(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizePositiveInt(int $value, string $message): int
    {
        if ($value < 1) {
            throw new InvalidArgumentException($message);
        }

        return $value;
    }
}
