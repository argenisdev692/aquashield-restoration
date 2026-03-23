<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Domain\Entities;

use Carbon\CarbonImmutable;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxFileName;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxPath;

final class FileEsx
{
    public function __construct(
        public readonly FileEsxId       $id,
        public readonly FileEsxFileName $fileName,
        public readonly FileEsxPath     $filePath,
        public readonly int             $uploadedBy,
        public readonly ?CarbonImmutable $createdAt = null,
        public readonly ?CarbonImmutable $updatedAt = null,
    ) {}

    public function withFileName(FileEsxFileName $fileName): self
    {
        return clone($this, fileName: $fileName);
    }
}
