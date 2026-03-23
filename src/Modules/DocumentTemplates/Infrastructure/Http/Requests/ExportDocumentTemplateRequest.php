<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExportDocumentTemplateRequest extends FormRequest
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
            'format'        => ['nullable', 'string', 'in:excel,pdf'],
            'search'        => ['nullable', 'string', 'max:255'],
            'template_type' => ['nullable', 'string', 'max:100'],
            'date_from'     => ['nullable', 'date'],
            'date_to'       => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
