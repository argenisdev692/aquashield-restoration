<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExportZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format'    => ['sometimes', 'string', 'in:excel,pdf'],
            'search'    => ['nullable', 'string', 'max:255'],
            'zone_type' => ['nullable', 'string', 'in:interior,exterior,basement,attic,garage,crawlspace'],
            'status'    => ['nullable', 'string', 'in:active,deleted'],
            'date_from' => ['nullable', 'date', 'before_or_equal:date_to'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
