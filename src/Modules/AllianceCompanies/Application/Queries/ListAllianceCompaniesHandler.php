<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AllianceCompanies\Application\DTOs\AllianceCompanyData;
use Modules\AllianceCompanies\Application\DTOs\AllianceCompanyFilterData;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;

final class ListAllianceCompaniesHandler
{
    public function handle(AllianceCompanyFilterData $filters): LengthAwarePaginator
    {
        $query = AllianceCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'alliance_company_name',
                'address',
                'phone',
                'email',
                'website',
                'user_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('alliance_company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('website', 'like', "%{$search}%");
                });
            })
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('alliance_company_name')
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (AllianceCompanyEloquentModel $allianceCompany): AllianceCompanyData => AllianceCompanyData::from([
                'uuid' => $allianceCompany->uuid,
                'allianceCompanyName' => $allianceCompany->alliance_company_name,
                'address' => $allianceCompany->address,
                'phone' => $allianceCompany->phone,
                'email' => $allianceCompany->email,
                'website' => $allianceCompany->website,
                'userId' => $allianceCompany->user_id,
                'createdAt' => $allianceCompany->created_at?->toIso8601String() ?? '',
                'updatedAt' => $allianceCompany->updated_at?->toIso8601String() ?? '',
                'deletedAt' => $allianceCompany->deleted_at?->toIso8601String(),
            ]));
    }
}
