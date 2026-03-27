<?php

declare(strict_types=1);

namespace Modules\Blog\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GeneratePostContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic'      => ['required', 'string', 'min:3', 'max:255'],
            'niche'      => ['required', 'string', 'min:2', 'max:100'],
            'word_count' => ['nullable', 'integer', 'min:300', 'max:5000'],
        ];
    }
}
