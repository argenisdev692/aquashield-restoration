export interface MortgageCompany {
    uuid: string;
    mortgage_company_name: string;
    address: string | null;
    address_2: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    user_id: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface MortgageCompanyFilters {
    search?: string;
    status?: string;
    date_from?: string;
    date_to?: string;
    page: number;
    per_page: number;
}

export interface PaginatedMortgageCompanyResponse {
    data: MortgageCompany[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface MortgageCompanyFormData {
    mortgage_company_name: string;
    address: string | null;
    address_2: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
}
