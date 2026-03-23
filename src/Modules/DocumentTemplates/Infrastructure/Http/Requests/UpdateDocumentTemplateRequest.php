<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateDocumentTemplateRequest extends FormRequest
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
            'template_name'        => ['required', 'string', 'max:255'],
            'template_description' => ['nullable', 'string', 'max:1000'],
            'template_type'        => ['required', 'string', 'max:100'],
            'template_path'        => ['nullable', 'file', 'mimes:doc,docx', 'max:20480'],
        ];
    }
}
