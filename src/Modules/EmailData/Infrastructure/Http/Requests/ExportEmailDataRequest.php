<?php

declare(strict_types=1);

namespace Modules\EmailData\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExportEmailDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format' => ['sometimes', 'string', 'in:excel,pdf'],
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,deleted'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];
    }
}
