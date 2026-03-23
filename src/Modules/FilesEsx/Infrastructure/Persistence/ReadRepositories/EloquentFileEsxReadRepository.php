<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Persistence\ReadRepositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\FilesEsx\Application\DTOs\FileEsxFilterData;
use Src\Modules\FilesEsx\Application\Queries\Contracts\FileEsxReadRepository;
use Src\Modules\FilesEsx\Domain\Ports\FileStoragePort;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileEsxEloquentModel;

final class EloquentFileEsxReadRepository implements FileEsxReadRepository
{
    public function __construct(
        private readonly FileStoragePort $storage,
    ) {}

    public function paginate(FileEsxFilterData $filters): LengthAwarePaginator
    {
        return FileEsxEloquentModel::query()
            ->with(['uploader:id,name,email', 'assignedAdjusters:id,name,email'])
            ->search($filters->search)
            ->when($filters->uploadedBy, static fn ($q, int $userId) => $q->where('uploaded_by', $userId))
            ->inDateRange($filters->dateFrom, $filters->dateTo)
            ->orderByDesc('created_at')
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(fn (FileEsxEloquentModel $model): array => $this->toReadModel($model));
    }

    public function findByUuid(string $uuid): ?array
    {
        $model = FileEsxEloquentModel::query()
            ->with([
                'uploader:id,name,email',
                'assignedAdjusters:id,name,email',
                'assignments.publicAdjuster:id,name,email',
                'assignments.assigner:id,name,email',
            ])
            ->where('uuid', $uuid)
            ->first();

        return $model !== null ? $this->toReadModel($model) : null;
    }

    public function findPathsByUuids(array $uuids): array
    {
        return FileEsxEloquentModel::query()
            ->whereIn('uuid', $uuids)
            ->pluck('file_path')
            ->all();
    }

    private function toReadModel(FileEsxEloquentModel $model): array
    {
        return [
            'id'                 => $model->id,
            'uuid'               => $model->uuid,
            'file_name'          => $model->file_name,
            'file_path'          => $model->file_path,
            'file_url'           => $this->storage->getUrl($model->file_path),
            'uploaded_by'        => $model->uploaded_by,
            'uploader'           => $model->relationLoaded('uploader') ? [
                'id'    => $model->uploader?->id,
                'name'  => $model->uploader?->name,
                'email' => $model->uploader?->email,
            ] : null,
            'assigned_adjusters' => $model->relationLoaded('assignedAdjusters')
                ? $model->assignedAdjusters->map(static fn ($u): array => [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'email' => $u->email,
                ])->values()->all()
                : [],
            'created_at'         => $model->created_at?->toIso8601String(),
            'updated_at'         => $model->updated_at?->toIso8601String(),
        ];
    }
}
