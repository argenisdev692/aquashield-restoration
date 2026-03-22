export interface DocumentTemplateAlliance {
    uuid: string;
    template_name_alliance: string;
    template_description_alliance: string | null;
    template_type_alliance: string;
    template_path_alliance: string;
    alliance_company_id: number;
    alliance_company_name: string | null;
    uploaded_by: number;
    uploaded_by_name: string | null;
    created_at: string;
    updated_at: string;
}

export interface DocumentTemplateAllianceFilters {
    search?: string;
    date_from?: string;
    date_to?: string;
    alliance_company_id?: number;
    template_type_alliance?: string;
    page?: number;
    per_page?: number;
}

export interface DocumentTemplateAllianceFormData {
    template_name_alliance: string;
    template_description_alliance: string;
    template_type_alliance: string;
    template_path_alliance: File | null;
    alliance_company_id: string;
}

export interface PaginatedDocumentTemplateAllianceResponse {
    data: DocumentTemplateAlliance[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
