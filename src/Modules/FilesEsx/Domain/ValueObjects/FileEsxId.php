<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Domain\ValueObjects;

final readonly class FileEsxId
{
    public string $value {
        get => $this->value;
        set {
            if (trim($value) === '') {
                throw new \InvalidArgumentException('FileEsxId cannot be empty.');
            }
            $this->value = $value;
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

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
