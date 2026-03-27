<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CampaignFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'   => ['nullable', 'string', 'max:255'],
            'status'   => ['nullable', 'string', 'in:draft,generated,published,deleted'],
            'platform' => ['nullable', 'string', 'in:tiktok,instagram,facebook'],
            'date_from'=> ['nullable', 'date'],
            'date_to'  => ['nullable', 'date'],
            'sort_by'  => ['nullable', 'string', 'in:title,platform,status,created_at,updated_at'],
            'sort_dir' => ['nullable', 'string', 'in:asc,desc'],
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
