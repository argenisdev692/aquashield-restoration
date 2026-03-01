<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PublicCompanyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->resource->getId()->value(),
            'public_company_name' => $this->resource->getPublicCompanyName(),
            'address' => $this->resource->getAddress(),
            'phone' => $this->resource->getPhone(),
            'email' => $this->resource->getEmail(),
            'website' => $this->resource->getWebsite(),
            'unit' => $this->resource->getUnit(),
            'user_id' => $this->resource->getUserId(),
            'created_at' => $this->resource->getCreatedAt(),
            'updated_at' => $this->resource->getUpdatedAt(),
            'deleted_at' => $this->resource->getDeletedAt(),
        ];
    }
}
