<?php

declare(strict_types=1);

namespace Modules\EmailData\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreEmailDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('email_data', 'email')],
            'phone' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:255'],
        ];
    }
}
