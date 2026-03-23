<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Domain\Ports;

interface FileAssignmentRepositoryPort
{
    public function upsert(string $fileUuid, int $publicAdjusterId, int $assignedBy): void;
}
