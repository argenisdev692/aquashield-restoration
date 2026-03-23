<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Application\Commands;

use Src\Modules\FilesEsx\Application\DTOs\AssignFileEsxData;
use Src\Modules\FilesEsx\Domain\Ports\FileAssignmentRepositoryPort;

final class AssignFileEsxHandler
{
    public function __construct(
        private readonly FileAssignmentRepositoryPort $assignmentRepository,
    ) {}

    public function handle(string $uuid, AssignFileEsxData $data): void
    {
        $this->assignmentRepository->upsert($uuid, $data->publicAdjusterId, $data->assignedBy);
    }
}
