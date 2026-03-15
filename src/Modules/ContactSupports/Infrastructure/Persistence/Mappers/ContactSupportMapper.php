<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Infrastructure\Persistence\Mappers;

use Src\Modules\ContactSupports\Domain\Entities\ContactSupport;
use Src\Modules\ContactSupports\Domain\ValueObjects\ContactSupportId;
use Src\Modules\ContactSupports\Infrastructure\Persistence\Eloquent\Models\ContactSupportEloquentModel;

final class ContactSupportMapper
{
    public function toDomain(ContactSupportEloquentModel $model): ContactSupport
    {
        return ContactSupport::reconstitute(
            id: ContactSupportId::fromString($model->uuid),
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
            phone: $model->phone,
            message: $model->message,
            smsConsent: (bool) $model->sms_consent,
            readed: (bool) $model->readed,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(ContactSupport $contactSupport): ContactSupportEloquentModel
    {
        $model = ContactSupportEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $contactSupport->id()->toString(),
        ]);

        $model->uuid = $contactSupport->id()->toString();
        $model->first_name = $contactSupport->firstName();
        $model->last_name = $contactSupport->lastName();
        $model->email = $contactSupport->email();
        $model->phone = $contactSupport->phone();
        $model->message = $contactSupport->message();
        $model->sms_consent = $contactSupport->smsConsent();
        $model->readed = $contactSupport->readed();

        return $model;
    }
}
