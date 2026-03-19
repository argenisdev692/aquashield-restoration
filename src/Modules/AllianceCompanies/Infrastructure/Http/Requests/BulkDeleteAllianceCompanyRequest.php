<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BulkDeleteAllianceCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'uuid'],
        ];
    }
}
