<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BulkDeletePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuids'   => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ];
    }
}
