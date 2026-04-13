<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExportInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format'         => ['sometimes', 'string', 'in:excel,pdf'],
            'search'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'status'         => ['sometimes', 'nullable', 'string', 'in:active,deleted'],
            'invoice_status' => ['sometimes', 'nullable', 'string', 'in:draft,sent,paid,cancelled,print_pdf'],
            'date_from'      => ['sometimes', 'nullable', 'date'],
            'date_to'        => ['sometimes', 'nullable', 'date', 'after_or_equal:date_from'],
            'claim_id'       => ['sometimes', 'nullable', 'integer'],
        ];
    }
}
