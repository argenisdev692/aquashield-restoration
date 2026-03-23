<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Persistence\Mappers;

use Carbon\CarbonImmutable;
use Src\Modules\FilesEsx\Domain\Entities\FileEsx;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxFileName;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxPath;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileEsxEloquentModel;

final class FileEsxMapper
{
    public function toDomain(FileEsxEloquentModel $model): FileEsx
    {
        return new FileEsx(
            id: FileEsxId::fromString($model->uuid),
            fileName: FileEsxFileName::fromNullable($model->file_name),
            filePath: FileEsxPath::fromString($model->file_path),
            uploadedBy: (int) $model->uploaded_by,
            createdAt: $model->created_at !== null
                ? CarbonImmutable::parse($model->created_at)
                : null,
            updatedAt: $model->updated_at !== null
                ? CarbonImmutable::parse($model->updated_at)
                : null,
        );
    }

    public function toEloquent(FileEsx $fileEsx): FileEsxEloquentModel
    {
        $model = FileEsxEloquentModel::where('uuid', $fileEsx->id->toString())
            ->firstOrNew();

        $model->uuid        = $fileEsx->id->toString();
        $model->file_name   = $fileEsx->fileName->toNullableString();
        $model->file_path   = $fileEsx->filePath->toString();
        $model->uploaded_by = $fileEsx->uploadedBy;

        return $model;
    }
}
