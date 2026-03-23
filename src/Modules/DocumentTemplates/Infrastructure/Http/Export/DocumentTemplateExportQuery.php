<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Src\Modules\DocumentTemplates\Application\DTOs\DocumentTemplateFilterData;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateEloquentModel;

final class DocumentTemplateExportQuery
{
    public static function build(DocumentTemplateFilterData $filters): Builder
    {
        return DocumentTemplateEloquentModel::query()
            ->with(['uploadedByUser'])
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('template_name', 'like', "%{$search}%")
                        ->orWhere('template_description', 'like', "%{$search}%")
                        ->orWhere('template_type', 'like', "%{$search}%");
                });
            })
            ->when($filters->templateType, static fn (Builder $b, string $type) => $b->where('template_type', $type))
            ->when($filters->dateFrom, static fn (Builder $b, string $d) => $b->whereDate('created_at', '>=', $d))
            ->when($filters->dateTo, static fn (Builder $b, string $d) => $b->whereDate('created_at', '<=', $d))
            ->orderBy('template_name');
    }
}
