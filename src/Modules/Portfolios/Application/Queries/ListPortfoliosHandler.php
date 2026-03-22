<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Portfolios\Application\DTOs\PortfolioFilterData;
use Src\Modules\Portfolios\Application\Queries\ReadModels\PortfolioListReadModel;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioEloquentModel;

final class ListPortfoliosHandler
{
    public function handle(PortfolioFilterData $filters): LengthAwarePaginator
    {
        $query = PortfolioEloquentModel::query()
            ->withTrashed()
            ->select([
                'portfolios.uuid',
                'portfolios.project_type_id',
                'portfolios.created_at',
                'portfolios.deleted_at',
                'project_types.uuid as project_type_uuid',
                'project_types.title as project_type_title',
                'service_categories.category as service_category_name',
            ])
            ->leftJoin('project_types', 'project_types.id', '=', 'portfolios.project_type_id')
            ->leftJoin('service_categories', 'service_categories.id', '=', 'project_types.service_category_id')
            ->withCount('images')
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('project_types.title', 'like', "%{$search}%")
                        ->orWhere('service_categories.category', 'like', "%{$search}%");
                });
            })
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('portfolios.deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->projectTypeUuid, static fn ($builder, string $uuid) => $builder->where('project_types.uuid', $uuid))
            ->when($filters->dateFrom, static fn ($builder, string $d) => $builder->whereDate('portfolios.created_at', '>=', $d))
            ->when($filters->dateTo, static fn ($builder, string $d) => $builder->whereDate('portfolios.created_at', '<=', $d))
            ->orderByDesc('portfolios.created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (PortfolioEloquentModel $m): PortfolioListReadModel => new PortfolioListReadModel(
                uuid: $m->uuid,
                projectTypeUuid: $m->project_type_uuid ?? null,
                projectTypeTitle: $m->project_type_title ?? null,
                serviceCategoryName: $m->service_category_name ?? null,
                imageCount: (int) ($m->images_count ?? 0),
                firstImagePath: $m->images()->orderBy('order')->value('path'),
                createdAt: $m->created_at?->toIso8601String() ?? '',
                deletedAt: $m->deleted_at?->toIso8601String(),
            ));
    }
}
