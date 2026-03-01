<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Persistence\Repositories;

use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\Ports\AllianceCompanyRepositoryPort;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;
use Modules\AllianceCompanies\Infrastructure\Persistence\Mappers\AllianceCompanyMapper;

final class EloquentAllianceCompanyRepository implements AllianceCompanyRepositoryPort
{
    private const SELECT_COLUMNS = [
        'id',
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
    ];

    public function find(AllianceCompanyId $id): ?AllianceCompany
    {
        return $this->findByUuid($id->value());
    }

    public function findByUuid(string $uuid): ?AllianceCompany
    {
        $model = AllianceCompanyEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->where('uuid', $uuid)
            ->first();

        return $model ? AllianceCompanyMapper::toDomain($model) : null;
    }

    public function save(AllianceCompany $AllianceCompany): void
    {
        AllianceCompanyEloquentModel::query()->updateOrCreate(
            ['uuid' => $AllianceCompany->getId()->value()],
            [
                'alliance_company_name' => $AllianceCompany->getAllianceCompanyName(),
                'address' => $AllianceCompany->getAddress(),
                'phone' => $AllianceCompany->getPhone(),
                'email' => $AllianceCompany->getEmail(),
                'website' => $AllianceCompany->getWebsite(),
                'user_id' => $AllianceCompany->getUserId(),
            ]
        );
    }

    public function delete(AllianceCompanyId $id): void
    {
        AllianceCompanyEloquentModel::query()->where('uuid', $id->value())->delete();
    }

    public function restore(AllianceCompanyId $id): void
    {
        AllianceCompanyEloquentModel::query()->withTrashed()->where('uuid', $id->value())->restore();
    }

    public function list(array $filters = []): array
    {
        $perPage = (int) ($filters['perPage'] ?? 15);
        $page = (int) ($filters['page'] ?? 1);

        $query = AllianceCompanyEloquentModel::query()
            ->select(self::SELECT_COLUMNS)
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) => $q->where('alliance_company_name', 'like', "%{$search}%")
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
                fn(AllianceCompanyEloquentModel $model) => AllianceCompanyMapper::toDomain($model),
                $paginator->items()
            ),
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }
}
