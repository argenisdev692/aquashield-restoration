<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

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
        'sms_consent' => 'boolean',
        'insurance_property' => 'boolean',
        'registration_date' => 'datetime',
        'inspection_date' => 'date',
        'inspection_time' => 'datetime:H:i:s',
        'intent_to_claim' => 'boolean',
        'follow_up_date' => 'date',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    // Agregar atributos predeterminados
    protected $attributes = [
        'status_lead' => 'New',
        'inspection_status' => 'Pending'
    ];

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
} 