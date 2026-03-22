<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Mappers;

use Src\Modules\DocumentTemplateAlliances\Domain\Entities\DocumentTemplateAlliance;
use Src\Modules\DocumentTemplateAlliances\Domain\ValueObjects\DocumentTemplateAllianceId;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAllianceEloquentModel;

final class DocumentTemplateAllianceMapper
{
    public function toDomain(DocumentTemplateAllianceEloquentModel $model): DocumentTemplateAlliance
    {
        return DocumentTemplateAlliance::reconstitute(
            id: DocumentTemplateAllianceId::fromString($model->uuid),
            templateNameAlliance: $model->template_name_alliance,
            templateDescriptionAlliance: $model->template_description_alliance,
            templateTypeAlliance: $model->template_type_alliance,
            templatePathAlliance: $model->template_path_alliance,
            allianceCompanyId: $model->alliance_company_id,
            uploadedBy: $model->uploaded_by,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
        );
    }

    public function toEloquent(DocumentTemplateAlliance $entity): DocumentTemplateAllianceEloquentModel
    {
        $model = DocumentTemplateAllianceEloquentModel::query()
            ->firstOrNew(['uuid' => $entity->id()->toString()]);

        $model->uuid                          = $entity->id()->toString();
        $model->template_name_alliance        = $entity->templateNameAlliance();
        $model->template_description_alliance = $entity->templateDescriptionAlliance();
        $model->template_type_alliance        = $entity->templateTypeAlliance();
        $model->template_path_alliance        = $entity->templatePathAlliance();
        $model->alliance_company_id           = $entity->allianceCompanyId();
        $model->uploaded_by                   = $entity->uploadedBy();

        return $model;
    }
}
