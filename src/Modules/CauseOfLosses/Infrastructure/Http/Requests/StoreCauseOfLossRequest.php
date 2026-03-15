<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCauseOfLossRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cause_loss_name' => ['required', 'string', 'max:255', Rule::unique('cause_of_losses', 'cause_loss_name')],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'string', Rule::in(['low', 'medium', 'high'])],
        ];
    }
}
