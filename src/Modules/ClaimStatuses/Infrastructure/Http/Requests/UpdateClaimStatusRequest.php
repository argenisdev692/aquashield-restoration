<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Eloquent\Models\ClaimStatusEloquentModel;

final class UpdateClaimStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $current = ClaimStatusEloquentModel::withTrashed()
            ->select(['id'])
            ->where('uuid', (string) $this->route('uuid'))
            ->first();

        return [
            'claim_status_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('claim_status', 'claim_status_name')->ignore($current?->id),
            ],
            'background_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
