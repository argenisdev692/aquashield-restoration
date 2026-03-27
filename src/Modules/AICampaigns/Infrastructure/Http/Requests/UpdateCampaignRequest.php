<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['sometimes', 'string', 'max:255'],
            'niche'          => ['sometimes', 'string', 'max:255'],
            'platform'       => ['sometimes', 'string', 'in:tiktok,instagram,facebook'],
            'caption'        => ['nullable', 'string'],
            'hashtags'       => ['nullable', 'string'],
            'call_to_action' => ['nullable', 'string', 'max:255'],
            'image_path'     => ['nullable', 'string'],
            'image_url'      => ['nullable', 'string', 'url'],
            'status'         => ['nullable', 'string', 'in:draft,generated,published'],
        ];
    }
}
