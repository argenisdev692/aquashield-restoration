<?php

declare(strict_types=1);

namespace Modules\EmailData\Infrastructure\Persistence\Mappers;

use Modules\EmailData\Domain\Entities\EmailData;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;
use Modules\EmailData\Infrastructure\Persistence\Eloquent\Models\EmailDataEloquentModel;

final class EmailDataMapper
{
    public function toDomain(EmailDataEloquentModel $model): EmailData
    {
        return EmailData::reconstitute(
            id: EmailDataId::fromString($model->uuid),
            description: $model->description,
            email: $model->email,
            phone: $model->phone,
            type: $model->type,
            userId: (int) $model->user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(EmailData $emailData): EmailDataEloquentModel
    {
        $model = EmailDataEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $emailData->id()->toString(),
        ]);

        $model->uuid = $emailData->id()->toString();
        $model->description = $emailData->description();
        $model->email = $emailData->email();
        $model->phone = $emailData->phone();
        $model->type = $emailData->type();
        $model->user_id = $emailData->userId();

        return $model;
    }
}
