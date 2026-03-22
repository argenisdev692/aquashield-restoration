<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExportDocumentTemplateAllianceRequest extends FormRequest
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
            'format'                 => ['nullable', 'string', 'in:excel,pdf'],
            'search'                 => ['nullable', 'string', 'max:255'],
            'date_from'              => ['nullable', 'date'],
            'date_to'                => ['nullable', 'date', 'after_or_equal:date_from'],
            'alliance_company_id'    => ['nullable', 'integer', 'min:1'],
            'template_type_alliance' => ['nullable', 'string', 'max:100'],
        ];
    }
}
