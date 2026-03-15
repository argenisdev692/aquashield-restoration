<?php

declare(strict_types=1);

namespace Modules\Roles\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'uuid' => data_get($this->resource, 'uuid'),
            'name' => data_get($this->resource, 'name'),
            'guard_name' => data_get($this->resource, 'guard_name'),
            'permissions_count' => data_get($this->resource, 'permissions_count', 0),
            'permission_names' => data_get($this->resource, 'permission_names', []),
            'created_at' => data_get($this->resource, 'created_at'),
            'updated_at' => data_get($this->resource, 'updated_at'),
            'deleted_at' => data_get($this->resource, 'deleted_at'),
        ];
    }
}
