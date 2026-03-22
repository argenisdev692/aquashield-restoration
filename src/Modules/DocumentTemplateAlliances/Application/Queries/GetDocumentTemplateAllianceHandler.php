<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Application\Queries;

use Src\Modules\DocumentTemplateAlliances\Application\DTOs\DocumentTemplateAllianceData;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAllianceEloquentModel;

final class GetDocumentTemplateAllianceHandler
{
    public function handle(string $uuid): ?DocumentTemplateAllianceData
    {
        $model = DocumentTemplateAllianceEloquentModel::query()
            ->with(['allianceCompany', 'uploadedByUser'])
            ->where('uuid', $uuid)
            ->first();

        if ($model === null) {
            return null;
        }

        return DocumentTemplateAllianceData::from([
            'uuid'                           => $model->uuid,
            'templateNameAlliance'           => $model->template_name_alliance,
            'templateDescriptionAlliance'    => $model->template_description_alliance,
            'templateTypeAlliance'           => $model->template_type_alliance,
            'templatePathAlliance'           => $model->template_path_alliance,
            'allianceCompanyId'              => $model->alliance_company_id,
            'allianceCompanyName'            => $model->allianceCompany?->alliance_company_name,
            'uploadedBy'                     => $model->uploaded_by,
            'uploadedByName'                 => $model->uploadedByUser?->name,
            'createdAt'                      => $model->created_at?->toIso8601String() ?? '',
            'updatedAt'                      => $model->updated_at?->toIso8601String() ?? '',
        ]);
    }
}
