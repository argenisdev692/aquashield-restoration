<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Persistence\Mappers;

use Src\Modules\Portfolios\Domain\Entities\Portfolio;
use Src\Modules\Portfolios\Domain\ValueObjects\PortfolioId;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioEloquentModel;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

final class PortfolioMapper
{
    public function toDomain(PortfolioEloquentModel $model): Portfolio
    {
        $projectTypeUuid = $model->project_type_id !== null
            ? ProjectTypeEloquentModel::withTrashed()
                ->where('id', $model->project_type_id)
                ->value('uuid')
            : null;

        return Portfolio::reconstitute(
            id: PortfolioId::fromString($model->uuid),
            projectTypeUuid: $projectTypeUuid,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(Portfolio $portfolio): PortfolioEloquentModel
    {
        $model = PortfolioEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $portfolio->id()->toString(),
        ]);

        $model->uuid = $portfolio->id()->toString();

        if ($portfolio->projectTypeUuid() !== null) {
            $projectType = ProjectTypeEloquentModel::where('uuid', $portfolio->projectTypeUuid())->first();
            $model->project_type_id = $projectType?->id;
        } else {
            $model->project_type_id = null;
        }

        return $model;
    }
}
