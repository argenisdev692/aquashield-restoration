<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Persistence\Repositories;

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Mappers\InsuranceCompanyMapper;

final class EloquentInsuranceCompanyRepository implements InsuranceCompanyRepositoryPort
{
    private const SELECT_COLUMNS = [
        'id',
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
    ];

    public function find(InsuranceCompanyId $id): ?InsuranceCompany
    {
        return $this->findByUuid($id->value());
    }

    public function findByUuid(string $uuid): ?InsuranceCompany
    {
        $model = InsuranceCompanyEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->where('uuid', $uuid)
            ->first();

        return $model ? InsuranceCompanyMapper::toDomain($model) : null;
    }

    public function save(InsuranceCompany $insuranceCompany): void
    {
        InsuranceCompanyEloquentModel::query()->updateOrCreate(
            ['uuid' => $insuranceCompany->getId()->value()],
            [
                'insurance_company_name' => $insuranceCompany->getInsuranceCompanyName(),
                'address' => $insuranceCompany->getAddress(),
                'phone' => $insuranceCompany->getPhone(),
                'email' => $insuranceCompany->getEmail(),
                'website' => $insuranceCompany->getWebsite(),
                'user_id' => $insuranceCompany->getUserId(),
            ]
        );
    }

    public function delete(InsuranceCompanyId $id): void
    {
        InsuranceCompanyEloquentModel::query()->where('uuid', $id->value())->delete();
    }

    public function restore(InsuranceCompanyId $id): void
    {
        InsuranceCompanyEloquentModel::query()->withTrashed()->where('uuid', $id->value())->restore();
    }

    public function list(array $filters = []): array
    {
        $perPage = (int) ($filters['perPage'] ?? 15);
        $page = (int) ($filters['page'] ?? 1);

        $query = InsuranceCompanyEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) => $q->where('insurance_company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            )
            ->when(
                ($filters['dateFrom'] ?? null) || ($filters['dateTo'] ?? null),
                fn($q) => $q->inDateRange($filters['dateFrom'] ?? null, $filters['dateTo'] ?? null),
            )
            ->when(
                isset($filters['onlyTrashed']) && $filters['onlyTrashed'] === 'true',
                fn($q) => $q->onlyTrashed()
            )
            ->orderBy($filters['sortBy'] ?? 'created_at', $filters['sortDir'] ?? 'desc');

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        return [
            'data' => array_map(
                fn(InsuranceCompanyEloquentModel $model) => InsuranceCompanyMapper::toDomain($model),
                $paginator->items()
            ),
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }
}
