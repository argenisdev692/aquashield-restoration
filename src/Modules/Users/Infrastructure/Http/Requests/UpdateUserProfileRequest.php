<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => 'nullable|string|max:1000',
            'visibility' => 'nullable|string|in:public,private,friends_only',
            'social_links' => 'nullable|array',
            'social_links.twitter' => 'nullable|url|max:255',
            'social_links.linkedin' => 'nullable|url|max:255',
            'social_links.github' => 'nullable|url|max:255',
            'social_links.website' => 'nullable|url|max:255',
        ];
    }
}
