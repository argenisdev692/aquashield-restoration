export interface AllianceCompany {
    company_id: number;
    uuid: string;
    alliance_company_name: string;
    address: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    user_id: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface AllianceCompanyFilters {
    search?: string;
    status?: 'active' | 'deleted';
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface AllianceCompanyFormData {
    alliance_company_name: string;
    address: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
}

export interface PaginatedAllianceCompanyResponse {
    data: AllianceCompany[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
