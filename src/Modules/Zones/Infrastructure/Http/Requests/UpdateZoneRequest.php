<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uuid = $this->route('uuid');

        return [
            'zone_name'   => ['required', 'string', 'max:255'],
            'zone_type'   => ['sometimes', 'string', 'in:interior,exterior,basement,attic,garage,crawlspace'],
            'code'        => ['nullable', 'string', 'max:50', "unique:zones,code,{$uuid},uuid"],
            'description' => ['nullable', 'string', 'max:1000'],
            'user_id'     => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
