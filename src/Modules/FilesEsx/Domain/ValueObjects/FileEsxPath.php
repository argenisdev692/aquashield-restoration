<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Domain\ValueObjects;

final readonly class FileEsxPath
{
    public string $value {
        get => $this->value;
        set {
            $normalized = trim($value);
            if ($normalized === '') {
                throw new \InvalidArgumentException('FileEsxPath cannot be empty.');
            }
            $this->value = $normalized;
        }
    }

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
