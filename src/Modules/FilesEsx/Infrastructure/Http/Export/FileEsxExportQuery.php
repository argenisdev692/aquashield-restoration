<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\FilesEsx\Application\DTOs\FileEsxFilterData;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileEsxEloquentModel;

final class FileEsxExportQuery
{
    public static function build(FileEsxFilterData $filters): Builder
    {
        return FileEsxEloquentModel::query()
            ->with(['uploader:id,name,email', 'assignedAdjusters:id,name,email'])
            ->withTrashed()
            ->select(['id', 'uuid', 'file_name', 'file_path', 'uploaded_by', 'created_at', 'deleted_at'])
            ->when($filters->status === 'active', static fn (Builder $q): Builder => $q->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $q): Builder => $q->onlyTrashed())
            ->search($filters->search)
            ->when($filters->uploadedBy, static fn (Builder $q, int $userId): Builder => $q->where('uploaded_by', $userId))
            ->inDateRange($filters->dateFrom, $filters->dateTo)
            ->orderByDesc('created_at');
    }
}
