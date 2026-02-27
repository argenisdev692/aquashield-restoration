<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Infrastructure\Persistence\Repositories;

use Src\Contexts\CompanyData\Infrastructure\Persistence\Eloquent\Models\CompanyDataEloquentModel;
use Src\Contexts\CompanyData\Application\DTOs\CompanyDataFilterDTO;
use Src\Contexts\CompanyData\Domain\Entities\CompanyData;
use Src\Contexts\CompanyData\Domain\Exceptions\CompanyDataNotFoundException;
use Src\Contexts\CompanyData\Domain\Ports\CompanyDataRepositoryPort;
use Src\Contexts\CompanyData\Domain\ValueObjects\CompanyDataId;
use Src\Contexts\CompanyData\Domain\ValueObjects\UserId;

final class EloquentCompanyDataRepository implements CompanyDataRepositoryPort
{
    public function findById(CompanyDataId $id): ?CompanyData
    {
        $model = CompanyDataEloquentModel::withTrashed()->where('uuid', $id->value)->first();

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findByUserId(UserId $userId): ?CompanyData
    {
        $model = CompanyDataEloquentModel::withTrashed()->where('user_id', $userId->value)->first();

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function save(CompanyData $companyData): void
    {
        $model = CompanyDataEloquentModel::withTrashed()->where('uuid', $companyData->id->value)->first() ?? new CompanyDataEloquentModel();

        $model->uuid = $companyData->id->value;
        $model->user_id = $companyData->userId->value;
        $model->name = $companyData->name;
        $model->company_name = $companyData->companyName;
        $model->email = $companyData->email;
        $model->phone = $companyData->phone;
        $model->address = $companyData->address;
        $model->website = $companyData->website;
        $model->facebook_link = $companyData->facebookLink;
        $model->instagram_link = $companyData->instagramLink;
        $model->linkedin_link = $companyData->linkedinLink;
        $model->twitter_link = $companyData->twitterLink;
        $model->latitude = $companyData->latitude;
        $model->longitude = $companyData->longitude;
        $model->signature_path = $companyData->signaturePath;

        if ($companyData->deletedAt !== null && $model->deleted_at === null) {
            $model->deleted_at = $companyData->deletedAt;
        } elseif ($companyData->deletedAt === null && $model->deleted_at !== null) {
            $model->deleted_at = null;
        }

        $model->save();
    }

    public function delete(CompanyDataId $id): void
    {
        CompanyDataEloquentModel::where('uuid', $id->value)->delete();
    }

    public function restore(CompanyDataId $id): void
    {
        CompanyDataEloquentModel::withTrashed()->where('uuid', $id->value)->restore();
    }

    public function paginate(CompanyDataFilterDTO $filters): array
    {
        $query = CompanyDataEloquentModel::query()
            ->select([
                'id',
                'uuid',
                'user_id',
                'name',
                'company_name',
                'email',
                'phone',
                'address',
                'website',
                'created_at',
                'updated_at'
            ])
            ->whereNull('deleted_at')
            ->when($filters->userId, fn($q) => $q->where('user_id', $filters->userId))
            ->when($filters->search, fn($q) => $q->where('company_name', 'like', "%{$filters->search}%"))
            ->when(
                $filters->dateFrom || $filters->dateTo,
                fn($q) => $q->inDateRange($filters->dateFrom, $filters->dateTo)
            )
            ->orderBy($filters->sortBy ?? 'created_at', $filters->sortDir ?? 'desc');

        $paginator = $query->paginate($filters->perPage ?? 15, ['*'], 'page', $filters->page ?? 1);

        $mappedItems = collect($paginator->items())->map(fn(CompanyDataEloquentModel $model) => [
            'id' => $model->uuid,
            'userId' => $model->user_id,
            'name' => $model->name,
            'companyName' => $model->company_name,
            'email' => $model->email,
            'phone' => $model->phone,
            'address' => $model->address,
            'website' => $model->website,
            'createdAt' => $model->created_at?->toIso8601String(),
        ])->toArray();

        return [
            'data' => $mappedItems,
            'meta' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    private function toDomain(CompanyDataEloquentModel $model): CompanyData
    {
        return new CompanyData(
            id: CompanyDataId::fromString($model->uuid),
            userId: UserId::fromInt($model->user_id),
            name: $model->name,
            companyName: $model->company_name,
            email: $model->email,
            phone: $model->phone,
            address: $model->address,
            website: $model->website,
            facebookLink: $model->facebook_link,
            instagramLink: $model->instagram_link,
            linkedinLink: $model->linkedin_link,
            twitterLink: $model->twitter_link,
            latitude: $model->latitude ? (float) $model->latitude : null,
            longitude: $model->longitude ? (float) $model->longitude : null,
            signaturePath: $model->signature_path,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }
}
