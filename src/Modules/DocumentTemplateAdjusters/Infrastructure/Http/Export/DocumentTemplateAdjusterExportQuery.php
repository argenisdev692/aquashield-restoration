<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\DocumentTemplateAdjusterFilterData;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAdjusterEloquentModel;

final class DocumentTemplateAdjusterExportQuery
{
    public static function build(DocumentTemplateAdjusterFilterData $filters): Builder
    {
        return DocumentTemplateAdjusterEloquentModel::query()
            ->with([
                'publicAdjuster:id,name',
                'uploadedByUser:id,name',
            ])
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('template_description_adjuster', 'like', "%{$search}%")
                        ->orWhere('template_type_adjuster', 'like', "%{$search}%");
                });
            })
            ->when($filters->publicAdjusterId, static fn (Builder $b, int $id) => $b->where('public_adjuster_id', $id))
            ->when($filters->templateTypeAdjuster, static fn (Builder $b, string $type) => $b->where('template_type_adjuster', $type))
            ->when($filters->dateFrom, static fn (Builder $b, string $d) => $b->whereDate('created_at', '>=', $d))
            ->when($filters->dateTo, static fn (Builder $b, string $d) => $b->whereDate('created_at', '<=', $d))
            ->orderBy('template_type_adjuster');
    }
}
