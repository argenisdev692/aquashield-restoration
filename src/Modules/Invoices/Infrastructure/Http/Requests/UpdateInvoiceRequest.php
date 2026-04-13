<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uuid = $this->route('uuid');

        return [
            'claim_id'          => ['nullable', 'integer', 'exists:claims,id'],
            'invoice_number'    => ['required', 'string', 'max:50', "unique:invoices,invoice_number,{$uuid},uuid"],
            'invoice_date'      => ['required', 'date'],
            'bill_to_name'      => ['required', 'string', 'max:255'],
            'bill_to_address'   => ['nullable', 'string'],
            'bill_to_phone'     => ['nullable', 'string', 'max:20'],
            'bill_to_email'     => ['nullable', 'email', 'max:100'],
            'subtotal'          => ['required', 'numeric', 'min:0'],
            'tax_amount'        => ['required', 'numeric', 'min:0'],
            'balance_due'       => ['required', 'numeric', 'min:0'],
            'claim_number'      => ['nullable', 'string', 'max:255'],
            'policy_number'     => ['nullable', 'string', 'max:255'],
            'insurance_company' => ['nullable', 'string', 'max:255'],
            'date_of_loss'      => ['nullable', 'date'],
            'date_received'     => ['nullable', 'date'],
            'date_inspected'    => ['nullable', 'date'],
            'date_entered'      => ['nullable', 'date'],
            'price_list_code'   => ['nullable', 'string', 'max:255'],
            'type_of_loss'      => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string'],
            'status'            => ['required', 'string', 'in:draft,sent,paid,cancelled,print_pdf'],
            'items'             => ['sometimes', 'array'],
            'items.*.service_name' => ['required_with:items', 'string', 'max:255'],
            'items.*.description'  => ['required_with:items', 'string'],
            'items.*.quantity'     => ['required_with:items', 'integer', 'min:1'],
            'items.*.rate'         => ['required_with:items', 'numeric', 'min:0'],
            'items.*.amount'       => ['sometimes', 'numeric', 'min:0'],
            'items.*.sort_order'   => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
