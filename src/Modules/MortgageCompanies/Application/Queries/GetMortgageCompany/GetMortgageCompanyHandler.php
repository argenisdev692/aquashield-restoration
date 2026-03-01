<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Queries\GetMortgageCompany;

use Illuminate\Support\Facades\Cache;
use Modules\MortgageCompanies\Application\Queries\ReadModels\MortgageCompanyReadModel;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;
use Src\Shared\Domain\Exceptions\EntityNotFoundException;

final readonly class GetMortgageCompanyHandler
{
    public function handle(string $uuid): MortgageCompanyReadModel
    {
        $cacheKey = "mortgage_company_{$uuid}";
        $ttl = 60 * 5;

        return Cache::remember($cacheKey, $ttl, function () use ($uuid) {
            $model = MortgageCompanyEloquentModel::withTrashed()
                ->where('uuid', $uuid)
                ->first();

            if (!$model) {
                throw new EntityNotFoundException("Mortgage company not found: {$uuid}");
            }

            return new MortgageCompanyReadModel(
                uuid: $model->uuid,
                mortgageCompanyName: $model->mortgage_company_name,
                address: $model->address,
                phone: $model->phone,
                email: $model->email,
                website: $model->website,
                userId: $model->user_id,
                createdAt: $model->created_at?->toIso8601String() ?? '',
                updatedAt: $model->updated_at?->toIso8601String() ?? '',
                deletedAt: $model->deleted_at?->toIso8601String(),
            );
        });
    }
}
