<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Persistence\Mappers;

use Src\Modules\DocumentTemplates\Domain\Entities\DocumentTemplate;
use Src\Modules\DocumentTemplates\Domain\ValueObjects\DocumentTemplateId;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateEloquentModel;

final class DocumentTemplateMapper
{
    public function toDomain(DocumentTemplateEloquentModel $model): DocumentTemplate
    {
        return DocumentTemplate::reconstitute(
            id: DocumentTemplateId::fromString($model->uuid),
            templateName: $model->template_name,
            templateDescription: $model->template_description,
            templateType: $model->template_type,
            templatePath: $model->template_path,
            uploadedBy: $model->uploaded_by,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
        );
    }

    public function toEloquent(DocumentTemplate $entity): DocumentTemplateEloquentModel
    {
        $model = DocumentTemplateEloquentModel::query()
            ->firstOrNew(['uuid' => $entity->id()->toString()]);

        $model->uuid                 = $entity->id()->toString();
        $model->template_name        = $entity->templateName();
        $model->template_description = $entity->templateDescription();
        $model->template_type        = $entity->templateType();
        $model->template_path        = $entity->templatePath();
        $model->uploaded_by          = $entity->uploadedBy();

        return $model;
    }
}
