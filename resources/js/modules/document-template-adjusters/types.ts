export interface DocumentTemplateAdjuster {
    uuid: string;
    template_description_adjuster: string | null;
    template_type_adjuster: string;
    template_path_adjuster: string;
    public_adjuster_id: number;
    public_adjuster_name: string | null;
    uploaded_by: number;
    uploaded_by_name: string | null;
    created_at: string;
    updated_at: string;
}

export interface DocumentTemplateAdjusterFilters {
    search?: string;
    date_from?: string;
    date_to?: string;
    public_adjuster_id?: number;
    template_type_adjuster?: string;
    page?: number;
    per_page?: number;
}

export interface DocumentTemplateAdjusterFormData {
    template_description_adjuster: string;
    template_type_adjuster: string;
    template_path_adjuster: File | null;
    public_adjuster_id: string;
}

export interface PaginatedDocumentTemplateAdjusterResponse {
    data: DocumentTemplateAdjuster[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export const ADJUSTER_TEMPLATE_TYPES = [
    { value: 'contract', label: 'Contract' },
    { value: 'estimate', label: 'Estimate' },
    { value: 'report', label: 'Report' },
    { value: 'authorization', label: 'Authorization' },
    { value: 'claim_form', label: 'Claim Form' },
    { value: 'addendum', label: 'Addendum' },
    { value: 'invoice', label: 'Invoice' },
    { value: 'other', label: 'Other' },
] as const;
