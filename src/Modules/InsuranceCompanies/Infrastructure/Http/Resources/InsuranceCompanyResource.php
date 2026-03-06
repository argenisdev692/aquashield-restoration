<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\InsuranceCompanies\Application\Queries\ReadModels\InsuranceCompanyListReadModel;
use Modules\InsuranceCompanies\Application\Queries\ReadModels\InsuranceCompanyReadModel;

final class InsuranceCompanyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = $this->resource;

        if ($resource instanceof InsuranceCompanyReadModel) {
            return [
                'uuid' => $resource->uuid,
                'insurance_company_name' => $resource->insuranceCompanyName,
                'address' => $resource->address,
                'phone' => $resource->phone,
                'email' => $resource->email,
                'website' => $resource->website,
                'user_id' => $resource->userId,
                'created_at' => $resource->createdAt,
                'updated_at' => $resource->updatedAt,
                'deleted_at' => $resource->deletedAt,
            ];
        }

        if ($resource instanceof InsuranceCompanyListReadModel) {
            return [
                'uuid' => $resource->uuid,
                'insurance_company_name' => $resource->insuranceCompanyName,
                'address' => $resource->address,
                'phone' => $resource->phone,
                'email' => $resource->email,
                'website' => $resource->website,
                'created_at' => $resource->createdAt,
                'deleted_at' => $resource->deletedAt,
            ];
        }

        // Fallback for domain entity (CreateHandler returns entity)
        return [
            'uuid' => $resource->getId()->value(),
            'insurance_company_name' => $resource->getInsuranceCompanyName(),
            'address' => $resource->getAddress(),
            'phone' => $resource->getPhone(),
            'email' => $resource->getEmail(),
            'website' => $resource->getWebsite(),
            'user_id' => $resource->getUserId(),
            'created_at' => $resource->getCreatedAt(),
            'updated_at' => $resource->getUpdatedAt(),
            'deleted_at' => $resource->getDeletedAt(),
        ];
    }
}
