<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GenerateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'    => ['required', 'string', 'max:255'],
            'niche'    => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', 'in:tiktok,instagram,facebook'],
        ];
    }
}
