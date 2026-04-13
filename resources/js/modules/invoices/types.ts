export type InvoiceStatus = 'draft' | 'sent' | 'paid' | 'cancelled' | 'print_pdf';

export interface InvoiceItem {
    uuid: string;
    invoice_id: number;
    service_name: string;
    description: string;
    quantity: number;
    rate: number;
    amount: number;
    sort_order: number;
}

export interface InvoiceListItem {
    uuid: string;
    invoice_number: string;
    invoice_date: string;
    bill_to_name: string;
    bill_to_email: string | null;
    bill_to_phone: string | null;
    subtotal: number;
    tax_amount: number;
    balance_due: number;
    status: InvoiceStatus;
    claim_number: string | null;
    insurance_company: string | null;
    items_count: number;
    created_at: string;
    deleted_at: string | null;
}

export interface Invoice {
    uuid: string;
    user_id: number;
    claim_id: number | null;
    invoice_number: string;
    invoice_date: string;
    bill_to_name: string;
    bill_to_address: string | null;
    bill_to_email: string | null;
    bill_to_phone: string | null;
    subtotal: number;
    tax_amount: number;
    balance_due: number;
    status: InvoiceStatus;
    claim_number: string | null;
    policy_number: string | null;
    insurance_company: string | null;
    date_of_loss: string | null;
    date_received: string | null;
    date_inspected: string | null;
    date_entered: string | null;
    price_list_code: string | null;
    type_of_loss: string | null;
    notes: string | null;
    pdf_url: string | null;
    items: InvoiceItem[];
    created_at: string;
    deleted_at: string | null;
}

export interface InvoiceFilters {
    search?: string;
    status?: 'active' | 'deleted';
    invoice_status?: InvoiceStatus;
    date_from?: string;
    date_to?: string;
    claim_id?: number;
    per_page?: number;
    page?: number;
}

export interface PaginatedInvoiceResponse {
    data: InvoiceListItem[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

export interface InvoiceItemPayload {
    service_name: string;
    description: string;
    quantity: number;
    rate: number;
    amount: number;
    sort_order: number;
}

export interface StoreInvoicePayload {
    user_id: number;
    invoice_number: string;
    invoice_date: string;
    bill_to_name: string;
    claim_id?: number | null;
    bill_to_address?: string | null;
    bill_to_phone?: string | null;
    bill_to_email?: string | null;
    subtotal: number;
    tax_amount: number;
    balance_due: number;
    claim_number?: string | null;
    policy_number?: string | null;
    insurance_company?: string | null;
    date_of_loss?: string | null;
    date_received?: string | null;
    date_inspected?: string | null;
    date_entered?: string | null;
    price_list_code?: string | null;
    type_of_loss?: string | null;
    notes?: string | null;
    status?: InvoiceStatus;
    items?: InvoiceItemPayload[];
}
