<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Application\Queries;

use Src\Modules\DocumentTemplates\Application\DTOs\DocumentTemplateData;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateEloquentModel;

final class GetDocumentTemplateHandler
{
    public function handle(string $uuid): ?DocumentTemplateData
    {
        $model = DocumentTemplateEloquentModel::query()
            ->with(['uploadedByUser'])
            ->where('uuid', $uuid)
            ->first();

        if ($model === null) {
            return null;
        }

        return DocumentTemplateData::from([
            'uuid'                => $model->uuid,
            'templateName'        => $model->template_name,
            'templateDescription' => $model->template_description,
            'templateType'        => $model->template_type,
            'templatePath'        => $model->template_path,
            'uploadedBy'          => $model->uploaded_by,
            'uploadedByName'      => $model->uploadedByUser?->name,
            'createdAt'           => $model->created_at?->toIso8601String() ?? '',
            'updatedAt'           => $model->updated_at?->toIso8601String() ?? '',
        ]);
    }
}
