<?php

declare(strict_types=1);

namespace Modules\EmailData\Infrastructure\Persistence\Repositories;

use Modules\EmailData\Domain\Entities\EmailData;
use Modules\EmailData\Domain\Ports\EmailDataRepositoryPort;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;
use Modules\EmailData\Infrastructure\Persistence\Eloquent\Models\EmailDataEloquentModel;
use Modules\EmailData\Infrastructure\Persistence\Mappers\EmailDataMapper;

final class EloquentEmailDataRepository implements EmailDataRepositoryPort
{
    public function __construct(
        private readonly EmailDataMapper $mapper,
    ) {}

    public function find(EmailDataId $id): ?EmailData
    {
        $model = EmailDataEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(EmailData $emailData): void
    {
        $this->mapper->toEloquent($emailData)->save();
    }

    public function softDelete(EmailDataId $id): void
    {
        EmailDataEloquentModel::query()->where('uuid', $id->toString())->delete();
    }

    public function restore(EmailDataId $id): void
    {
        EmailDataEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (EmailDataId $id): string => $id->toString(),
            $ids,
        );

        return EmailDataEloquentModel::query()->whereIn('uuid', $uuids)->delete();
    }
}
