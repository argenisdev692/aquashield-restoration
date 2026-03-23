<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Domain\ValueObjects;

final readonly class FileEsxFileName
{
    public ?string $value {
        get => $this->value;
        set {
            $this->value = $value !== null ? trim($value) : null;
        }
    }

    public function __construct(?string $value)
    {
        $this->value = $value;
    }

    public static function fromNullable(?string $value): self
    {
        return new self($value);
    }

    public function toNullableString(): ?string
    {
        return $this->value;
    }
}
