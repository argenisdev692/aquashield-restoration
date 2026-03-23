<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\DocumentTemplates\Domain\ValueObjects\DocumentTemplateId;

final class DocumentTemplate extends AggregateRoot
{
    private function __construct(
        private DocumentTemplateId $id,
        private string $templateName,
        private ?string $templateDescription,
        private string $templateType,
        private string $templatePath,
        private int $uploadedBy,
        private string $createdAt,
        private string $updatedAt,
    ) {
        $this->templateName        = self::normalizeRequired($templateName, 'Template name is required.');
        $this->templateType        = self::normalizeRequired($templateType, 'Template type is required.');
        $this->templatePath        = self::normalizeRequired($templatePath, 'Template path is required.');
        $this->templateDescription = self::normalizeNullable($templateDescription);
        $this->uploadedBy          = self::normalizePositiveInt($uploadedBy, 'Uploaded by user is required.');
    }

    public static function create(
        DocumentTemplateId $id,
        string $templateName,
        ?string $templateDescription,
        string $templateType,
        string $templatePath,
        int $uploadedBy,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            templateName: $templateName,
            templateDescription: $templateDescription,
            templateType: $templateType,
            templatePath: $templatePath,
            uploadedBy: $uploadedBy,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        DocumentTemplateId $id,
        string $templateName,
        ?string $templateDescription,
        string $templateType,
        string $templatePath,
        int $uploadedBy,
        string $createdAt,
        string $updatedAt,
    ): self {
        return new self(
            id: $id,
            templateName: $templateName,
            templateDescription: $templateDescription,
            templateType: $templateType,
            templatePath: $templatePath,
            uploadedBy: $uploadedBy,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function update(
        string $templateName,
        ?string $templateDescription,
        string $templateType,
        string $templatePath,
        string $updatedAt,
    ): void {
        $this->templateName        = self::normalizeRequired($templateName, 'Template name is required.');
        $this->templateType        = self::normalizeRequired($templateType, 'Template type is required.');
        $this->templatePath        = self::normalizeRequired($templatePath, 'Template path is required.');
        $this->templateDescription = self::normalizeNullable($templateDescription);
        $this->updatedAt           = $updatedAt;
    }

    public function id(): DocumentTemplateId { return $this->id; }
    public function templateName(): string { return $this->templateName; }
    public function templateDescription(): ?string { return $this->templateDescription; }
    public function templateType(): string { return $this->templateType; }
    public function templatePath(): string { return $this->templatePath; }
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
