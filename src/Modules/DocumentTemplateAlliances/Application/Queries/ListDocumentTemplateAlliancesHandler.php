<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\DocumentTemplateAllianceData;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\DocumentTemplateAllianceFilterData;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAllianceEloquentModel;

final class ListDocumentTemplateAlliancesHandler
{
    public function handle(DocumentTemplateAllianceFilterData $filters): LengthAwarePaginator
    {
        $query = DocumentTemplateAllianceEloquentModel::query()
            ->with(['allianceCompany', 'uploadedByUser'])
            ->select([
                'uuid',
                'template_name_alliance',
                'template_description_alliance',
                'template_type_alliance',
                'template_path_alliance',
                'alliance_company_id',
                'uploaded_by',
                'created_at',
                'updated_at',
            ])
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('template_name_alliance', 'like', "%{$search}%")
                        ->orWhere('template_description_alliance', 'like', "%{$search}%")
                        ->orWhere('template_type_alliance', 'like', "%{$search}%");
                });
            })
            ->when($filters->allianceCompanyId, static fn ($builder, int $id) => $builder->where('alliance_company_id', $id))
            ->when($filters->templateTypeAlliance, static fn ($builder, string $type) => $builder->where('template_type_alliance', $type))
            ->when($filters->dateFrom, static fn ($builder, string $d) => $builder->whereDate('created_at', '>=', $d))
            ->when($filters->dateTo, static fn ($builder, string $d) => $builder->whereDate('created_at', '<=', $d))
            ->orderBy('template_name_alliance')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (DocumentTemplateAllianceEloquentModel $m): DocumentTemplateAllianceData => DocumentTemplateAllianceData::from([
                'uuid'                        => $m->uuid,
                'templateNameAlliance'        => $m->template_name_alliance,
                'templateDescriptionAlliance' => $m->template_description_alliance,
                'templateTypeAlliance'        => $m->template_type_alliance,
                'templatePathAlliance'        => $m->template_path_alliance,
                'allianceCompanyId'           => $m->alliance_company_id,
                'allianceCompanyName'         => $m->allianceCompany?->alliance_company_name,
                'uploadedBy'                  => $m->uploaded_by,
                'uploadedByName'              => $m->uploadedByUser?->name,
                'createdAt'                   => $m->created_at?->toIso8601String() ?? '',
                'updatedAt'                   => $m->updated_at?->toIso8601String() ?? '',
            ]));
    }
}
