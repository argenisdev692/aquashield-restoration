<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CallHistoryExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('VIEW_CALL_HISTORY') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'format' => ['nullable', 'string', Rule::in(['excel', 'pdf'])],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['active', 'deleted'])],
            'direction' => ['nullable', 'string', Rule::in(['inbound', 'outbound'])],
            'call_type' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'format.in' => 'The format must be either excel or pdf.',
            'status.in' => 'The status must be either active or deleted.',
            'direction.in' => 'The direction must be either inbound or outbound.',
            'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }
}
