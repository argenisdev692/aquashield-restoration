<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDocumentTemplateAllianceRequest extends FormRequest
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
            'template_name_alliance'        => ['required', 'string', 'max:255'],
            'template_description_alliance' => ['nullable', 'string', 'max:1000'],
            'template_type_alliance'        => ['required', 'string', 'max:100'],
            'template_path_alliance'        => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:20480'],
            'alliance_company_id'           => ['required', 'integer', 'min:1', 'exists:alliance_companies,id'],
        ];
    }
}
