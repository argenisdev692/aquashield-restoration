<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class AppointmentEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'appointments';

    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'address_2',
        'city',
        'state',
        'zipcode',
        'country',
        'insurance_property',
        'message',
        'sms_consent',
        'registration_date',
        'inspection_date',
        'inspection_time',
        'notes',
        'owner',
        'damage_detail',
        'intent_to_claim',
        'lead_source',
        'follow_up_date',
        'additional_note',
        'inspection_status',
        'status_lead',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'insurance_property' => 'boolean',
        'sms_consent' => 'boolean',
        'intent_to_claim' => 'boolean',
        'registration_date' => 'datetime',
        'inspection_date' => 'date',
        'inspection_time' => 'datetime:H:i:s',
        'follow_up_date' => 'date',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    protected $attributes = [
        'status_lead' => 'New',
        'inspection_status' => 'Pending',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'first_name',
                'last_name',
                'phone',
                'email',
                'address',
                'address_2',
                'city',
                'state',
                'zipcode',
                'country',
                'insurance_property',
                'message',
                'sms_consent',
                'registration_date',
                'inspection_date',
                'inspection_time',
                'notes',
                'owner',
                'damage_detail',
                'intent_to_claim',
                'lead_source',
                'follow_up_date',
                'additional_note',
                'inspection_status',
                'status_lead',
                'latitude',
                'longitude',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('appointments.appointment');
    }
}
