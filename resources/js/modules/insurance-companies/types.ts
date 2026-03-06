export interface InsuranceCompany {
    uuid: string;
    insurance_company_name: string;
    address: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    user_id: number | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface InsuranceCompanyFilters {
    search?: string;
    status?: 'active' | 'deleted';
    dateFrom?: string;
    dateTo?: string;
    onlyTrashed?: boolean;
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    page?: number;
    perPage?: number;
}
