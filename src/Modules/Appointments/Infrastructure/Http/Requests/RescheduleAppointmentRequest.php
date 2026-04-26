<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'inspection_date' => ['required', 'date'],
            'inspection_time' => ['required', 'date_format:H:i'],
        ];
    }
}
