<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExportClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format'          => ['nullable', 'string', 'in:excel,pdf'],
            'search'          => ['nullable', 'string', 'max:255'],
            'status'          => ['nullable', 'string', 'in:active,deleted'],
            'date_from'       => ['nullable', 'date_format:Y-m-d'],
            'date_to'         => ['nullable', 'date_format:Y-m-d'],
            'claim_status_id' => ['nullable', 'integer'],
            'type_damage_id'  => ['nullable', 'integer'],
        ];
    }
}
