<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Persistence\Repositories;

use Src\Modules\FilesEsx\Domain\Ports\FileAssignmentRepositoryPort;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileAssignmentEloquentModel;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileEsxEloquentModel;

final class EloquentFileAssignmentRepository implements FileAssignmentRepositoryPort
{
    public function upsert(string $fileUuid, int $publicAdjusterId, int $assignedBy): void
    {
        $file = FileEsxEloquentModel::where('uuid', $fileUuid)->firstOrFail();

        FileAssignmentEloquentModel::updateOrCreate(
            [
                'file_id'            => $file->id,
                'public_adjuster_id' => $publicAdjusterId,
            ],
            [
                'assigned_by' => $assignedBy,
            ],
        );
    }
}
