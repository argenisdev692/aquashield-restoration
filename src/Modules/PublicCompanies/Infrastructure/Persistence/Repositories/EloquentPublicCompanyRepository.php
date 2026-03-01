<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Persistence\Repositories;

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\Ports\PublicCompanyRepositoryPort;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;
use Modules\PublicCompanies\Infrastructure\Persistence\Mappers\PublicCompanyMapper;

final class EloquentPublicCompanyRepository implements PublicCompanyRepositoryPort
{
    private const SELECT_COLUMNS = [
        'id',
        'uuid',
        'public_company_name',
        'address',
        'phone',
        'email',
        'website',
        'unit',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function find(PublicCompanyId $id): ?PublicCompany
    {
        return $this->findByUuid($id->value());
    }

    public function findByUuid(string $uuid): ?PublicCompany
    {
        $model = PublicCompanyEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->where('uuid', $uuid)
            ->first();

        return $model ? PublicCompanyMapper::toDomain($model) : null;
    }

    public function save(PublicCompany $PublicCompany): void
    {
        PublicCompanyEloquentModel::query()->updateOrCreate(
            ['uuid' => $PublicCompany->getId()->value()],
            [
                'public_company_name' => $PublicCompany->getPublicCompanyName(),
                'address' => $PublicCompany->getAddress(),
                'phone' => $PublicCompany->getPhone(),
                'email' => $PublicCompany->getEmail(),
                'website' => $PublicCompany->getWebsite(),
                'unit' => $PublicCompany->getUnit(),
                'user_id' => $PublicCompany->getUserId(),
            ]
        );
    }

    public function delete(PublicCompanyId $id): void
    {
        PublicCompanyEloquentModel::query()->where('uuid', $id->value())->delete();
    }

    public function restore(PublicCompanyId $id): void
    {
        PublicCompanyEloquentModel::query()->withTrashed()->where('uuid', $id->value())->restore();
    }

    public function list(array $filters = []): array
    {
        $perPage = (int) ($filters['perPage'] ?? 15);
        $page = (int) ($filters['page'] ?? 1);

        $query = PublicCompanyEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) => $q->where('public_company_name', 'like', "%{$search}%")
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
                fn(PublicCompanyEloquentModel $model) => PublicCompanyMapper::toDomain($model),
                $paginator->items()
            ),
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }
}
