<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Domain\Entities;

use InvalidArgumentException;
use Shared\Domain\Entities\AggregateRoot;
use Src\Modules\DocumentTemplateAlliances\Domain\ValueObjects\DocumentTemplateAllianceId;

final class DocumentTemplateAlliance extends AggregateRoot
{
    private function __construct(
        private DocumentTemplateAllianceId $id,
        private string $templateNameAlliance,
        private ?string $templateDescriptionAlliance,
        private string $templateTypeAlliance,
        private string $templatePathAlliance,
        private int $allianceCompanyId,
        private int $uploadedBy,
        private string $createdAt,
        private string $updatedAt,
    ) {
        $this->templateNameAlliance   = self::normalizeRequired($templateNameAlliance, 'Template name is required.');
        $this->templateTypeAlliance   = self::normalizeRequired($templateTypeAlliance, 'Template type is required.');
        $this->templatePathAlliance   = self::normalizeRequired($templatePathAlliance, 'Template path is required.');
        $this->templateDescriptionAlliance = self::normalizeNullable($templateDescriptionAlliance);
        $this->allianceCompanyId      = self::normalizePositiveInt($allianceCompanyId, 'Alliance company is required.');
        $this->uploadedBy             = self::normalizePositiveInt($uploadedBy, 'Uploaded by user is required.');
    }

    public static function create(
        DocumentTemplateAllianceId $id,
        string $templateNameAlliance,
        ?string $templateDescriptionAlliance,
        string $templateTypeAlliance,
        string $templatePathAlliance,
        int $allianceCompanyId,
        int $uploadedBy,
        string $createdAt,
    ): self {
        return new self(
            id: $id,
            templateNameAlliance: $templateNameAlliance,
            templateDescriptionAlliance: $templateDescriptionAlliance,
            templateTypeAlliance: $templateTypeAlliance,
            templatePathAlliance: $templatePathAlliance,
            allianceCompanyId: $allianceCompanyId,
            uploadedBy: $uploadedBy,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    public static function reconstitute(
        DocumentTemplateAllianceId $id,
        string $templateNameAlliance,
        ?string $templateDescriptionAlliance,
        string $templateTypeAlliance,
        string $templatePathAlliance,
        int $allianceCompanyId,
        int $uploadedBy,
        string $createdAt,
        string $updatedAt,
    ): self {
        return new self(
            id: $id,
            templateNameAlliance: $templateNameAlliance,
            templateDescriptionAlliance: $templateDescriptionAlliance,
            templateTypeAlliance: $templateTypeAlliance,
            templatePathAlliance: $templatePathAlliance,
            allianceCompanyId: $allianceCompanyId,
            uploadedBy: $uploadedBy,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function update(
        string $templateNameAlliance,
        ?string $templateDescriptionAlliance,
        string $templateTypeAlliance,
        string $templatePathAlliance,
        int $allianceCompanyId,
        string $updatedAt,
    ): void {
        $this->templateNameAlliance        = self::normalizeRequired($templateNameAlliance, 'Template name is required.');
        $this->templateTypeAlliance        = self::normalizeRequired($templateTypeAlliance, 'Template type is required.');
        $this->templatePathAlliance        = self::normalizeRequired($templatePathAlliance, 'Template path is required.');
        $this->templateDescriptionAlliance = self::normalizeNullable($templateDescriptionAlliance);
        $this->allianceCompanyId           = self::normalizePositiveInt($allianceCompanyId, 'Alliance company is required.');
        $this->updatedAt                   = $updatedAt;
    }

    public function id(): DocumentTemplateAllianceId { return $this->id; }
    public function templateNameAlliance(): string { return $this->templateNameAlliance; }
    public function templateDescriptionAlliance(): ?string { return $this->templateDescriptionAlliance; }
    public function templateTypeAlliance(): string { return $this->templateTypeAlliance; }
    public function templatePathAlliance(): string { return $this->templatePathAlliance; }
    public function allianceCompanyId(): int { return $this->allianceCompanyId; }
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
