<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Persistence\Mappers;

use Modules\AICampaigns\Domain\Entities\Campaign;
use Modules\AICampaigns\Domain\ValueObjects\CampaignId;
use Modules\AICampaigns\Infrastructure\Persistence\Eloquent\Models\CampaignEloquentModel;

final class CampaignMapper
{
    public static function toDomain(CampaignEloquentModel $model): Campaign
    {
        return new Campaign(
            id:           new CampaignId($model->id),
            uuid:         $model->uuid,
            title:        $model->title,
            niche:        $model->niche,
            platform:     $model->platform,
            caption:      $model->caption,
            hashtags:     $model->hashtags,
            callToAction: $model->call_to_action,
            imagePath:    $model->image_path,
            imageUrl:     $model->image_url,
            status:       $model->status,
            userId:       $model->user_id,
            createdAt:    $model->created_at?->toIso8601String(),
            updatedAt:    $model->updated_at?->toIso8601String(),
            deletedAt:    $model->deleted_at?->toIso8601String(),
        );
    }
}
