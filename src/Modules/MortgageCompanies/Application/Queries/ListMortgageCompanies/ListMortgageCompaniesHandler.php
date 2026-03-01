<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Queries\ListMortgageCompanies;

use Illuminate\Support\Facades\Cache;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterDTO;
use Modules\MortgageCompanies\Application\Queries\ReadModels\MortgageCompanyListReadModel;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;

final readonly class ListMortgageCompaniesHandler
{
    public function handle(MortgageCompanyFilterDTO $filters): array
    {
        $cacheKey = 'mortgage_companies_list_' . md5(json_encode($filters->toArray()));
        $ttl = 60 * 2;

        try {
            return Cache::tags(['mortgage_companies_list'])->remember($cacheKey, $ttl, fn() => $this->fetchData($filters));
        } catch (\Exception $e) {
            return Cache::remember($cacheKey, $ttl, fn() => $this->fetchData($filters));
        }
    }

    private function fetchData(MortgageCompanyFilterDTO $filters): array
    {
        $query = MortgageCompanyEloquentModel::query()
            ->select([
                'uuid',
                'mortgage_company_name',
                'address',
                'phone',
                'email',
                'website',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, fn($q, $search) => 
                $q->where(function ($query) use ($search) {
                    $query->where('mortgage_company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
            )
            ->when($filters->status === 'deleted', fn($q) => $q->onlyTrashed())
            ->when($filters->status === 'active', fn($q) => $q->whereNull('deleted_at'))
            ->when($filters->dateFrom, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters->dateTo, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->orderBy('created_at', 'desc');

        $result = $query->paginate($filters->perPage, ['*'], 'page', $filters->page);

        $result['data'] = array_map(
            fn($item) => new MortgageCompanyListReadModel(
                uuid: $item->uuid,
                mortgageCompanyName: $item->mortgage_company_name,
                address: $item->address,
                phone: $item->phone,
                email: $item->email,
                website: $item->website,
                createdAt: $item->created_at?->toIso8601String() ?? '',
                deletedAt: $item->deleted_at?->toIso8601String(),
            ),
            $result->items()
        );

        return [
            'data' => $result['data'],
            'meta' => [
                'total' => $result->total(),
                'currentPage' => $result->currentPage(),
                'lastPage' => $result->lastPage(),
                'perPage' => $result->perPage(),
            ],
        ];
    }
}
