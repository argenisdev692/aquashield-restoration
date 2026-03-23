export interface DocumentTemplate {
    uuid: string;
    template_name: string;
    template_description: string | null;
    template_type: string;
    template_path: string;
    uploaded_by: number;
    uploaded_by_name: string | null;
    created_at: string;
    updated_at: string;
}

export interface DocumentTemplateFilters {
    search?: string;
    template_type?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface DocumentTemplateFormData {
    template_name: string;
    template_description: string;
    template_type: string;
    template_path: File | null;
}

export interface PaginatedDocumentTemplateResponse {
    data: DocumentTemplate[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export const DOCUMENT_TEMPLATE_TYPES = [
    { value: 'contract', label: 'Contract' },
    { value: 'estimate', label: 'Estimate' },
    { value: 'invoice', label: 'Invoice' },
    { value: 'report', label: 'Report' },
    { value: 'form', label: 'Form' },
    { value: 'certificate', label: 'Certificate' },
    { value: 'other', label: 'Other' },
] as const;
