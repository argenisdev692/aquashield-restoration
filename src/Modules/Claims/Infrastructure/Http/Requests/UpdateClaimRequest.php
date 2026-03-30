<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id'        => ['required', 'integer', 'exists:properties,id'],
            'signature_path_id'  => ['nullable', 'integer'],
            'type_damage_id'     => ['required', 'integer', 'exists:type_damages,id'],
            'user_id_ref_by'     => ['required', 'integer', 'exists:users,id'],
            'claim_status'       => ['required', 'integer', 'exists:claim_status,id'],
            'policy_number'      => ['required', 'string', 'max:255'],
            'claim_number'       => ['nullable', 'string', 'max:255'],
            'date_of_loss'       => ['nullable', 'string', 'max:50'],
            'description_of_loss'=> ['nullable', 'string'],
            'number_of_floors'   => ['nullable', 'integer', 'min:0'],
            'claim_date'         => ['nullable', 'string', 'max:50'],
            'work_date'          => ['nullable', 'string', 'max:50'],
            'damage_description' => ['nullable', 'string'],
            'scope_of_work'      => ['nullable', 'string'],
            'customer_reviewed'  => ['nullable', 'boolean'],
            'cause_of_loss_ids'  => ['nullable', 'array'],
            'cause_of_loss_ids.*'=> ['integer', 'exists:cause_of_losses,id'],
            'service_request_ids'   => ['nullable', 'array'],
            'service_request_ids.*' => ['integer', 'exists:service_requests,id'],
        ];
    }
}
