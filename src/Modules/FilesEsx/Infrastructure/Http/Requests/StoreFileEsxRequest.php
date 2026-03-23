<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreFileEsxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file'      => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,esx,zip,txt', 'max:51200'],
            'file_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
