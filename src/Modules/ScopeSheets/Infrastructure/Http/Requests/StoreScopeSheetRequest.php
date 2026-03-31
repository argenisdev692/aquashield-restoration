<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreScopeSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'claim_id'                => ['required', 'integer', 'exists:claims,id'],
            'generated_by'            => ['required', 'integer', 'exists:users,id'],
            'scope_sheet_description' => ['nullable', 'string', 'max:1000'],
            'presentations'           => ['nullable', 'array'],
            'presentations.*.photo_type'  => ['required_with:presentations', 'string', 'max:100'],
            'presentations.*.photo_path'  => ['required_with:presentations', 'string', 'max:2048'],
            'presentations.*.photo_order' => ['required_with:presentations', 'integer', 'min:0'],
            'zones'                   => ['nullable', 'array'],
            'zones.*.zone_id'         => ['required_with:zones', 'integer', 'exists:zones,id'],
            'zones.*.zone_order'      => ['required_with:zones', 'integer', 'min:0'],
            'zones.*.zone_notes'      => ['nullable', 'string'],
            'zones.*.photos'          => ['nullable', 'array'],
            'zones.*.photos.*.photo_path'  => ['required_with:zones.*.photos', 'string', 'max:2048'],
            'zones.*.photos.*.photo_order' => ['required_with:zones.*.photos', 'integer', 'min:0'],
        ];
    }
}
