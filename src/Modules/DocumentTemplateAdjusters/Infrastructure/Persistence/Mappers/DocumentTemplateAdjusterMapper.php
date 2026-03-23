<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Mappers;

use Src\Modules\DocumentTemplateAdjusters\Domain\Entities\DocumentTemplateAdjuster;
use Src\Modules\DocumentTemplateAdjusters\Domain\ValueObjects\DocumentTemplateAdjusterId;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAdjusterEloquentModel;

final class DocumentTemplateAdjusterMapper
{
    public function toDomain(DocumentTemplateAdjusterEloquentModel $model): DocumentTemplateAdjuster
    {
        return DocumentTemplateAdjuster::reconstitute(
            id: DocumentTemplateAdjusterId::fromString($model->uuid),
            templateDescriptionAdjuster: $model->template_description_adjuster,
            templateTypeAdjuster: $model->template_type_adjuster,
            templatePathAdjuster: $model->template_path_adjuster,
            publicAdjusterId: $model->public_adjuster_id,
            uploadedBy: $model->uploaded_by,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
        );
    }

    public function toEloquent(DocumentTemplateAdjuster $entity): DocumentTemplateAdjusterEloquentModel
    {
        $model = DocumentTemplateAdjusterEloquentModel::query()
            ->firstOrNew(['uuid' => $entity->id()->toString()]);

        $model->uuid                          = $entity->id()->toString();
        $model->template_description_adjuster = $entity->templateDescriptionAdjuster();
        $model->template_type_adjuster        = $entity->templateTypeAdjuster();
        $model->template_path_adjuster        = $entity->templatePathAdjuster();
        $model->public_adjuster_id            = $entity->publicAdjusterId();
        $model->uploaded_by                   = $entity->uploadedBy();

        return $model;
    }
}
