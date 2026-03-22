<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateServiceRequestRequest extends FormRequest
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
            'requested_service' => ['required', 'string', 'max:255'],
        ];
    }
}
