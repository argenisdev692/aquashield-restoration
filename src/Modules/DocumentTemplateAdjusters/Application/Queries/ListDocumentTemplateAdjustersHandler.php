<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\DocumentTemplateAdjusterData;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\DocumentTemplateAdjusterFilterData;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAdjusterEloquentModel;

final class ListDocumentTemplateAdjustersHandler
{
    public function handle(DocumentTemplateAdjusterFilterData $filters): LengthAwarePaginator
    {
        $query = DocumentTemplateAdjusterEloquentModel::query()
            ->with([
                'publicAdjuster:id,name',
                'uploadedByUser:id,name',
            ])
            ->select([
                'id',
                'uuid',
                'template_description_adjuster',
                'template_type_adjuster',
                'template_path_adjuster',
                'public_adjuster_id',
                'uploaded_by',
                'created_at',
                'updated_at',
            ])
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('template_description_adjuster', 'like', "%{$search}%")
                        ->orWhere('template_type_adjuster', 'like', "%{$search}%");
                });
            })
            ->when($filters->publicAdjusterId, static fn ($builder, int $id) => $builder->where('public_adjuster_id', $id))
            ->when($filters->templateTypeAdjuster, static fn ($builder, string $type) => $builder->where('template_type_adjuster', $type))
            ->when($filters->dateFrom, static fn ($builder, string $d) => $builder->whereDate('created_at', '>=', $d))
            ->when($filters->dateTo, static fn ($builder, string $d) => $builder->whereDate('created_at', '<=', $d))
            ->orderBy('template_type_adjuster')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (DocumentTemplateAdjusterEloquentModel $m): DocumentTemplateAdjusterData => DocumentTemplateAdjusterData::from([
                'uuid'                          => $m->uuid,
                'templateDescriptionAdjuster'   => $m->template_description_adjuster,
                'templateTypeAdjuster'          => $m->template_type_adjuster,
                'templatePathAdjuster'          => $m->template_path_adjuster,
                'publicAdjusterId'              => $m->public_adjuster_id,
                'publicAdjusterName'            => $m->publicAdjuster?->name,
                'uploadedBy'                    => $m->uploaded_by,
                'uploadedByName'                => $m->uploadedByUser?->name,
                'createdAt'                     => $m->created_at?->toIso8601String() ?? '',
                'updatedAt'                     => $m->updated_at?->toIso8601String() ?? '',
            ]));
    }
}
