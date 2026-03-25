<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExportCustomerRequest extends FormRequest
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
            'format'    => ['sometimes', 'string', 'in:excel,pdf'],
            'search'    => ['nullable', 'string', 'max:255'],
            'status'    => ['nullable', 'string', 'in:active,deleted'],
            'date_from' => ['nullable', 'date', 'before_or_equal:date_to'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
