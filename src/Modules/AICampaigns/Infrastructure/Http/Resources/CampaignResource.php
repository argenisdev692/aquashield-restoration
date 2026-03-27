<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'            => $this->resource->uuid,
            'title'           => $this->resource->title,
            'niche'           => $this->resource->niche,
            'platform'        => $this->resource->platform,
            'caption'         => $this->resource->caption,
            'hashtags'        => $this->resource->hashtags,
            'call_to_action'  => $this->resource->callToAction,
            'image_path'      => $this->resource->imagePath,
            'image_url'       => $this->resource->imageUrl,
            'status'          => $this->resource->status,
            'user_id'         => $this->resource->userId,
            'created_at'      => $this->resource->createdAt,
            'updated_at'      => $this->resource->updatedAt,
            'deleted_at'      => $this->resource->deletedAt,
        ];
    }
}
