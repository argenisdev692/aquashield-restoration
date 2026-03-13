<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CheckUserAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', Rule::in(['email', 'username', 'phone'])],
            'value' => ['required', 'string', 'max:255'],
            'ignore_uuid' => ['nullable', 'uuid'],
        ];
    }
}
