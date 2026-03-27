<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Queries\ListCampaigns;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AICampaigns\Application\Queries\ReadModels\CampaignListReadModel;
use Modules\AICampaigns\Infrastructure\Persistence\Eloquent\Models\CampaignEloquentModel;

final readonly class ListCampaignsHandler
{
    #[\NoDiscard('Paginated campaign list must be returned to caller')]
    public function handle(ListCampaignsQuery $query): array
    {
        $filters = $query->filters;

        $builder = CampaignEloquentModel::query()
            ->when(
                $filters->status === 'deleted',
                fn ($q) => $q->onlyTrashed(),
                fn ($q) => $q->whereNull('deleted_at'),
            )
            ->when(
                $filters->search,
                fn ($q) => $q->where(function ($inner) use ($filters): void {
                    $inner->where('title', 'like', "%{$filters->search}%")
                        ->orWhere('niche', 'like', "%{$filters->search}%")
                        ->orWhere('caption', 'like', "%{$filters->search}%");
                }),
            )
            ->when(
                $filters->platform,
                fn ($q) => $q->where('platform', $filters->platform),
            )
            ->when(
                $filters->status && $filters->status !== 'deleted',
                fn ($q) => $q->where('status', $filters->status),
            )
            ->when(
                $filters->dateFrom,
                fn ($q) => $q->whereDate('created_at', '>=', $filters->dateFrom),
            )
            ->when(
                $filters->dateTo,
                fn ($q) => $q->whereDate('created_at', '<=', $filters->dateTo),
            )
            ->orderBy(
                in_array($filters->sortBy, ['title', 'platform', 'status', 'created_at', 'updated_at'], true)
                    ? $filters->sortBy
                    : 'created_at',
                $filters->sortDir === 'asc' ? 'asc' : 'desc',
            );

        /** @var LengthAwarePaginator $paginator */
        $paginator = $builder->paginate(
            perPage: min($filters->perPage, 100),
            page:    $filters->page,
        );

        $items = collect($paginator->items())
            ->map(fn (CampaignEloquentModel $m): CampaignListReadModel => new CampaignListReadModel(
                uuid:      $m->uuid,
                title:     $m->title,
                niche:     $m->niche,
                platform:  $m->platform,
                caption:   $m->caption,
                hashtags:  $m->hashtags,
                imageUrl:  $m->image_url,
                status:    $m->status,
                createdAt: $m->created_at?->toIso8601String(),
                updatedAt: $m->updated_at?->toIso8601String(),
                deletedAt: $m->deleted_at?->toIso8601String(),
            ))
            ->toArray();

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ];
    }
}
