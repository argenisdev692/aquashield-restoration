<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AssignFileEsxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'public_adjuster_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
