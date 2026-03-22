<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Application\Queries;

use Src\Modules\Portfolios\Application\Queries\ReadModels\PortfolioDetailReadModel;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioEloquentModel;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioImageEloquentModel;

final class GetPortfolioHandler
{
    public function handle(string $uuid): ?PortfolioDetailReadModel
    {
        $model = PortfolioEloquentModel::withTrashed()
            ->with([
                'images' => static fn ($q) => $q->orderBy('order')->select([
                    'id', 'uuid', 'portfolio_id', 'path', 'order',
                ]),
                'projectType:id,uuid,title,service_category_id',
                'projectType.serviceCategory:id,uuid,category',
            ])
            ->where('portfolios.uuid', $uuid)
            ->first();

        if ($model === null) {
            return null;
        }

        $images = $model->images->map(static fn (PortfolioImageEloquentModel $img): array => [
            'uuid'  => $img->uuid,
            'path'  => $img->path,
            'order' => $img->order,
        ])->values()->all();

        return new PortfolioDetailReadModel(
            uuid: $model->uuid,
            projectTypeUuid: $model->projectType?->uuid,
            projectTypeTitle: $model->projectType?->title,
            serviceCategoryName: $model->projectType?->serviceCategory?->category,
            images: $images,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }
}
