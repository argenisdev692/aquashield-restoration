<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Persistence\Repositories;

use Modules\AICampaigns\Domain\Entities\Campaign;
use Modules\AICampaigns\Domain\Exceptions\CampaignNotFoundException;
use Modules\AICampaigns\Domain\Ports\CampaignRepositoryPort;
use Modules\AICampaigns\Infrastructure\Persistence\Eloquent\Models\CampaignEloquentModel;
use Modules\AICampaigns\Infrastructure\Persistence\Mappers\CampaignMapper;

final class EloquentCampaignRepository implements CampaignRepositoryPort
{
    public function create(array $data): Campaign
    {
        $model = CampaignEloquentModel::create($data);

        return CampaignMapper::toDomain($model);
    }

    public function update(string $uuid, array $data): Campaign
    {
        $model = $this->findModel($uuid);
        $model->update($data);

        return CampaignMapper::toDomain($model->fresh() ?? $model);
    }

    public function findByUuid(string $uuid): Campaign
    {
        return CampaignMapper::toDomain($this->findModel($uuid));
    }

    public function softDelete(string $uuid): void
    {
        $this->findModel($uuid)->delete();
    }

    public function restore(string $uuid): void
    {
        $model = CampaignEloquentModel::withTrashed()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $model->restore();
    }

    public function bulkDelete(array $uuids): void
    {
        CampaignEloquentModel::whereIn('uuid', $uuids)->delete();
    }

    private function findModel(string $uuid): CampaignEloquentModel
    {
        $model = CampaignEloquentModel::where('uuid', $uuid)->first();

        if ($model === null) {
            throw new CampaignNotFoundException($uuid);
        }

        return $model;
    }
}
