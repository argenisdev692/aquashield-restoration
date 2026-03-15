<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Modules\CauseOfLosses\Infrastructure\Persistence\Eloquent\Models\CauseOfLossEloquentModel;

final class UpdateCauseOfLossRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentCauseOfLoss = CauseOfLossEloquentModel::withTrashed()
            ->select(['id'])
            ->where('uuid', (string) $this->route('uuid'))
            ->first();

        return [
            'cause_loss_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cause_of_losses', 'cause_loss_name')->ignore($currentCauseOfLoss?->id),
            ],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'string', Rule::in(['low', 'medium', 'high'])],
        ];
    }
}
