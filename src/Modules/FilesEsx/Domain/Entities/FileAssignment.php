<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Domain\Entities;

use Carbon\CarbonImmutable;

final class FileAssignment
{
    public function __construct(
        public readonly int $id,
        public readonly int $fileId,
        public readonly int $publicAdjusterId,
        public readonly int $assignedBy,
        public readonly ?CarbonImmutable $createdAt = null,
    ) {}
}
