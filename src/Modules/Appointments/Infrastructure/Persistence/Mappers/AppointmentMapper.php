<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Persistence\Mappers;

use Src\Modules\Appointments\Domain\Entities\Appointment;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;
use Src\Modules\Appointments\Infrastructure\Persistence\Eloquent\Models\AppointmentEloquentModel;

final class AppointmentMapper
{
    public function toDomain(AppointmentEloquentModel $model): Appointment
    {
        return Appointment::reconstitute(
            id: AppointmentId::fromString($model->uuid),
            firstName: $model->first_name,
            lastName: $model->last_name,
            phone: $model->phone,
            email: $model->email,
            address: $model->address,
            address2: $model->address_2,
            city: $model->city,
            state: $model->state,
            zipcode: $model->zipcode,
            country: $model->country,
            insuranceProperty: (bool) $model->insurance_property,
            message: $model->message,
            smsConsent: (bool) $model->sms_consent,
            registrationDate: $model->registration_date?->toDateString(),
            inspectionDate: $model->inspection_date?->toDateString(),
            inspectionTime: $model->inspection_time?->format('H:i'),
            notes: $model->notes,
            owner: $model->owner,
            damageDetail: $model->damage_detail,
            intentToClaim: (bool) $model->intent_to_claim,
            leadSource: $model->lead_source,
            followUpDate: $model->follow_up_date?->toDateString(),
            additionalNote: $model->additional_note,
            inspectionStatus: $model->inspection_status,
            statusLead: $model->status_lead,
            latitude: $model->latitude === null ? null : (float) $model->latitude,
            longitude: $model->longitude === null ? null : (float) $model->longitude,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(Appointment $appointment): AppointmentEloquentModel
    {
        $model = AppointmentEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $appointment->id()->toString(),
        ]);

        $model->uuid = $appointment->id()->toString();
        $model->first_name = $appointment->firstName();
        $model->last_name = $appointment->lastName();
        $model->phone = $appointment->phone();
        $model->email = $appointment->email();
        $model->address = $appointment->address();
        $model->address_2 = $appointment->address2();
        $model->city = $appointment->city();
        $model->state = $appointment->state();
        $model->zipcode = $appointment->zipcode();
        $model->country = $appointment->country();
        $model->insurance_property = $appointment->insuranceProperty();
        $model->message = $appointment->message();
        $model->sms_consent = $appointment->smsConsent();
        $model->registration_date = $appointment->registrationDate();
        $model->inspection_date = $appointment->inspectionDate();
        $model->inspection_time = $appointment->inspectionTime();
        $model->notes = $appointment->notes();
        $model->owner = $appointment->owner();
        $model->damage_detail = $appointment->damageDetail();
        $model->intent_to_claim = $appointment->intentToClaim();
        $model->lead_source = $appointment->leadSource();
        $model->follow_up_date = $appointment->followUpDate();
        $model->additional_note = $appointment->additionalNote();
        $model->inspection_status = $appointment->inspectionStatus();
        $model->status_lead = $appointment->statusLead();
        $model->latitude = $appointment->latitude();
        $model->longitude = $appointment->longitude();

        return $model;
    }
}
