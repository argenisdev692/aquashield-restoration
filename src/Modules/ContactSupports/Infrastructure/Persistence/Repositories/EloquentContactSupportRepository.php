<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Infrastructure\Persistence\Repositories;

use Src\Modules\ContactSupports\Domain\Entities\ContactSupport;
use Src\Modules\ContactSupports\Domain\Ports\ContactSupportRepositoryPort;
use Src\Modules\ContactSupports\Domain\ValueObjects\ContactSupportId;
use Src\Modules\ContactSupports\Infrastructure\Persistence\Eloquent\Models\ContactSupportEloquentModel;
use Src\Modules\ContactSupports\Infrastructure\Persistence\Mappers\ContactSupportMapper;

final class EloquentContactSupportRepository implements ContactSupportRepositoryPort
{
    public function __construct(
        private readonly ContactSupportMapper $mapper,
    ) {}

    public function find(ContactSupportId $id): ?ContactSupport
    {
        $model = ContactSupportEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(ContactSupport $contactSupport): void
    {
        $this->mapper->toEloquent($contactSupport)->save();
    }

    public function softDelete(ContactSupportId $id): void
    {
        ContactSupportEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(ContactSupportId $id): void
    {
        ContactSupportEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (ContactSupportId $id): string => $id->toString(),
            $ids,
        );

        return ContactSupportEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}
