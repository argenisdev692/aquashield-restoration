<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\DocumentTemplates\Application\DTOs\DocumentTemplateData;
use Src\Modules\DocumentTemplates\Application\DTOs\DocumentTemplateFilterData;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateEloquentModel;

final class ListDocumentTemplatesHandler
{
    public function handle(DocumentTemplateFilterData $filters): LengthAwarePaginator
    {
        $query = DocumentTemplateEloquentModel::query()
            ->with(['uploadedByUser'])
            ->select([
                'uuid',
                'template_name',
                'template_description',
                'template_type',
                'template_path',
                'uploaded_by',
                'created_at',
                'updated_at',
            ])
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('template_name', 'like', "%{$search}%")
                        ->orWhere('template_description', 'like', "%{$search}%")
                        ->orWhere('template_type', 'like', "%{$search}%");
                });
            })
            ->when($filters->templateType, static fn ($builder, string $type) => $builder->where('template_type', $type))
            ->when($filters->dateFrom, static fn ($builder, string $d) => $builder->whereDate('created_at', '>=', $d))
            ->when($filters->dateTo, static fn ($builder, string $d) => $builder->whereDate('created_at', '<=', $d))
            ->orderBy('template_name')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (DocumentTemplateEloquentModel $m): DocumentTemplateData => DocumentTemplateData::from([
                'uuid'                => $m->uuid,
                'templateName'        => $m->template_name,
                'templateDescription' => $m->template_description,
                'templateType'        => $m->template_type,
                'templatePath'        => $m->template_path,
                'uploadedBy'          => $m->uploaded_by,
                'uploadedByName'      => $m->uploadedByUser?->name,
                'createdAt'           => $m->created_at?->toIso8601String() ?? '',
                'updatedAt'           => $m->updated_at?->toIso8601String() ?? '',
            ]));
    }
}
