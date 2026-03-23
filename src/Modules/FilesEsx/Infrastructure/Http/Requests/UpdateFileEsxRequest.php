<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateFileEsxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
