<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompany;

use Illuminate\Support\Facades\Cache;
use Modules\InsuranceCompanies\Application\Queries\ReadModels\InsuranceCompanyReadModel;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;
use Shared\Domain\Exceptions\EntityNotFoundException;

final readonly class GetInsuranceCompanyHandler
{
    #[\NoDiscard('The InsuranceCompanyReadModel must be captured')]
    public function handle(GetInsuranceCompanyQuery $query): InsuranceCompanyReadModel
    {
        $cacheKey = "insurance_company_{$query->uuid}";

        $model = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query): ?InsuranceCompanyEloquentModel {
            return InsuranceCompanyEloquentModel::query()
                ->select([
                    'uuid',
                    'insurance_company_name',
                    'address',
                    'phone',
                    'email',
                    'website',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ])
                ->where('uuid', $query->uuid)
                ->first();
        });

        if (!$model) {
            throw new EntityNotFoundException("Insurance Company with UUID {$query->uuid} not found.");
        }

        return new InsuranceCompanyReadModel(
            uuid: $model->uuid,
            insuranceCompanyName: $model->insurance_company_name,
            address: $model->address,
            phone: $model->phone,
            email: $model->email,
            website: $model->website,
            userId: $model->user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }
}
