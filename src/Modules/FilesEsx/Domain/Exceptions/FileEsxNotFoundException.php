<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Domain\Exceptions;

use RuntimeException;

final class FileEsxNotFoundException extends RuntimeException
{
    public function __construct(string $uuid)
    {
        parent::__construct("FileEsx with UUID [{$uuid}] not found.");
    }

    public static function forUuid(string $uuid): self
    {
        return new self($uuid);
    }
}
