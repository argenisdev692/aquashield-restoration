<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AllianceCompanyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->resource->getId()->value(),
            'alliance_company_name' => $this->resource->getAllianceCompanyName(),
            'address' => $this->resource->getAddress(),
            'phone' => $this->resource->getPhone(),
            'email' => $this->resource->getEmail(),
            'website' => $this->resource->getWebsite(),
            'user_id' => $this->resource->getUserId(),
            'created_at' => $this->resource->getCreatedAt(),
            'updated_at' => $this->resource->getUpdatedAt(),
            'deleted_at' => $this->resource->getDeletedAt(),
        ];
    }
}
