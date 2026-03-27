<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'niche'          => ['required', 'string', 'max:255'],
            'platform'       => ['required', 'string', 'in:tiktok,instagram,facebook'],
            'caption'        => ['nullable', 'string'],
            'hashtags'       => ['nullable', 'string'],
            'call_to_action' => ['nullable', 'string', 'max:255'],
            'image_path'     => ['nullable', 'string'],
            'image_url'      => ['nullable', 'string', 'url'],
            'status'         => ['nullable', 'string', 'in:draft,generated,published'],
        ];
    }
}
