<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateContactSupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string'],
            'sms_consent' => ['sometimes', 'boolean'],
            'readed' => ['sometimes', 'boolean'],
        ];
    }
}
