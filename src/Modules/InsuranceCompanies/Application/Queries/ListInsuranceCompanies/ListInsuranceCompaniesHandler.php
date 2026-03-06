<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\ListInsuranceCompanies;

use Illuminate\Support\Facades\Cache;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterDTO;
use Modules\InsuranceCompanies\Application\Queries\ReadModels\InsuranceCompanyListReadModel;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;

final readonly class ListInsuranceCompaniesHandler
{
    /**
     * @return array{data: list<InsuranceCompanyListReadModel>, total: int, perPage: int, currentPage: int, lastPage: int}
     */
    public function handle(ListInsuranceCompaniesQuery $query, ?int $perPage = null): array
    {
        $filters = $query->filters;
        $effectivePerPage = $perPage ?? $filters->perPage;
        $cacheKey = 'insurance_companies_list_' . md5(serialize($filters) . $effectivePerPage);

        $fetchData = function () use ($filters, $effectivePerPage): array {
            $qb = InsuranceCompanyEloquentModel::query()
                ->select([
                    'uuid',
                    'insurance_company_name',
                    'address',
                    'phone',
                    'email',
                    'website',
                    'created_at',
                    'deleted_at',
                ])
                ->when(
                    $filters->search,
                    fn($q, $search) => $q->where(function ($q) use ($search): void {
                        $q->where('insurance_company_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    }),
                )
                ->when(
                    $filters->dateFrom || $filters->dateTo,
                    fn($q) => $q->inDateRange($filters->dateFrom, $filters->dateTo),
                )
                ->when(
                    $filters->onlyTrashed === 'true',
                    fn($q) => $q->onlyTrashed(),
                )
                ->orderBy($filters->sortBy ?? 'created_at', $filters->sortDir ?? 'desc');

            $paginator = $qb->paginate(perPage: $effectivePerPage, page: $filters->page);

            return [
                'data' => array_map(
                    fn(InsuranceCompanyEloquentModel $model) => new InsuranceCompanyListReadModel(
                        uuid: $model->uuid,
                        insuranceCompanyName: $model->insurance_company_name,
                        address: $model->address,
                        phone: $model->phone,
                        email: $model->email,
                        website: $model->website,
                        createdAt: $model->created_at?->toIso8601String() ?? '',
                        deletedAt: $model->deleted_at?->toIso8601String(),
                    ),
                    $paginator->items(),
                ),
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
            ];
        };

        try {
            return Cache::tags(['insurance_companies_list'])->remember($cacheKey, now()->addMinutes(10), $fetchData);
        } catch (\Exception) {
            return Cache::remember($cacheKey, now()->addMinutes(10), $fetchData);
        }
    }
}
