<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Application\Queries;

use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\DocumentTemplateAdjusterData;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAdjusterEloquentModel;

final class GetDocumentTemplateAdjusterHandler
{
    public function handle(string $uuid): ?DocumentTemplateAdjusterData
    {
        $model = DocumentTemplateAdjusterEloquentModel::query()
            ->with([
                'publicAdjuster:id,name',
                'uploadedByUser:id,name',
            ])
            ->where('uuid', $uuid)
            ->first();

        if ($model === null) {
            return null;
        }

        return DocumentTemplateAdjusterData::from([
            'uuid'                          => $model->uuid,
            'templateDescriptionAdjuster'   => $model->template_description_adjuster,
            'templateTypeAdjuster'          => $model->template_type_adjuster,
            'templatePathAdjuster'          => $model->template_path_adjuster,
            'publicAdjusterId'              => $model->public_adjuster_id,
            'publicAdjusterName'            => $model->publicAdjuster?->name,
            'uploadedBy'                    => $model->uploaded_by,
            'uploadedByName'                => $model->uploadedByUser?->name,
            'createdAt'                     => $model->created_at?->toIso8601String() ?? '',
            'updatedAt'                     => $model->updated_at?->toIso8601String() ?? '',
        ]);
    }
}
