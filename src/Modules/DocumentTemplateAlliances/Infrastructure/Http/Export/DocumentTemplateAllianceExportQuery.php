<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\DocumentTemplateAllianceFilterData;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAllianceEloquentModel;

final class DocumentTemplateAllianceExportQuery
{
    public static function build(DocumentTemplateAllianceFilterData $filters): Builder
    {
        return DocumentTemplateAllianceEloquentModel::query()
            ->with(['allianceCompany', 'uploadedByUser'])
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('template_name_alliance', 'like', "%{$search}%")
                        ->orWhere('template_description_alliance', 'like', "%{$search}%")
                        ->orWhere('template_type_alliance', 'like', "%{$search}%");
                });
            })
            ->when($filters->allianceCompanyId, static fn (Builder $b, int $id) => $b->where('alliance_company_id', $id))
            ->when($filters->templateTypeAlliance, static fn (Builder $b, string $type) => $b->where('template_type_alliance', $type))
            ->when($filters->dateFrom, static fn (Builder $b, string $d) => $b->whereDate('created_at', '>=', $d))
            ->when($filters->dateTo, static fn (Builder $b, string $d) => $b->whereDate('created_at', '<=', $d))
            ->orderBy('template_name_alliance');
    }
}
