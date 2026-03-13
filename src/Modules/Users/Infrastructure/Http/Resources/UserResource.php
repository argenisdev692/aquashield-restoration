<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="last_name", type="string", nullable=true),
 *     @OA\Property(property="full_name", type="string"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true),
 *     @OA\Property(property="username", type="string", nullable=true),
 *     @OA\Property(property="phone", type="string", nullable=true),
 *     @OA\Property(property="profile_photo_path", type="string", nullable=true),
 *     @OA\Property(property="address", type="string", nullable=true),
 *     @OA\Property(property="city", type="string", nullable=true),
 *     @OA\Property(property="state", type="string", nullable=true),
 *     @OA\Property(property="country", type="string", nullable=true),
 *     @OA\Property(property="zip_code", type="string", nullable=true),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="created_at", type="string", nullable=true),
 *     @OA\Property(property="updated_at", type="string", nullable=true)
 * )
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $name = data_get($this->resource, 'name');
        $lastName = data_get($this->resource, 'lastName') ?? data_get($this->resource, 'last_name');

        return [
            'id' => data_get($this->resource, 'id.value') ?? data_get($this->resource, 'id'),
            'uuid' => data_get($this->resource, 'uuid'),
            'name' => $name,
            'last_name' => $lastName,
            'full_name' => method_exists($this->resource, 'fullName')
                ? $this->resource->fullName()
                : trim(($name ?? '') . ' ' . ($lastName ?? '')),
            'email' => data_get($this->resource, 'email'),
            'username' => data_get($this->resource, 'username'),
            'phone' => data_get($this->resource, 'phone'),
            'profile_photo_path' => data_get($this->resource, 'profilePhotoPath') ?? data_get($this->resource, 'profile_photo_path'),
            'address' => data_get($this->resource, 'address'),
            'address_2' => data_get($this->resource, 'address2') ?? data_get($this->resource, 'address_2'),
            'city' => data_get($this->resource, 'city'),
            'state' => data_get($this->resource, 'state'),
            'country' => data_get($this->resource, 'country'),
            'zip_code' => data_get($this->resource, 'zipCode') ?? data_get($this->resource, 'zip_code'),
            'status' => data_get($this->resource, 'status.value') ?? data_get($this->resource, 'status') ?? 'active',
            'created_at' => data_get($this->resource, 'createdAt') ?? data_get($this->resource, 'created_at'),
            'updated_at' => data_get($this->resource, 'updatedAt') ?? data_get($this->resource, 'updated_at'),
            'deleted_at' => data_get($this->resource, 'deletedAt') ?? data_get($this->resource, 'deleted_at'),
        ];
    }
}
