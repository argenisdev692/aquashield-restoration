<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'inspection_status' => [
                'required',
                'string',
                Rule::in(['Pending', 'Confirmed', 'Declined', 'Completed']),
            ],
        ];
    }
}
