<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zipcode' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:255'],
            'insurance_property' => ['sometimes', 'boolean'],
            'message' => ['nullable', 'string'],
            'sms_consent' => ['sometimes', 'boolean'],
            'registration_date' => ['nullable', 'date'],
            'inspection_date' => ['nullable', 'date'],
            'inspection_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
            'owner' => ['nullable', 'string', 'max:255'],
            'damage_detail' => ['nullable', 'string'],
            'intent_to_claim' => ['sometimes', 'boolean'],
            'lead_source' => ['nullable', 'string', 'max:255'],
            'follow_up_date' => ['nullable', 'date'],
            'additional_note' => ['nullable', 'string'],
            'inspection_status' => ['nullable', 'string', 'max:255'],
            'status_lead' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ];
    }
}
