<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateDocumentTemplateAdjusterRequest extends FormRequest
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
            'template_description_adjuster' => ['nullable', 'string', 'max:2000'],
            'template_type_adjuster'        => ['required', 'string', 'max:100'],
            'template_path_adjuster'        => ['nullable', 'file', 'mimes:doc,docx,pdf', 'max:20480'],
            'public_adjuster_id'            => ['required', 'integer', 'min:1', 'exists:users,id'],
        ];
    }
}
