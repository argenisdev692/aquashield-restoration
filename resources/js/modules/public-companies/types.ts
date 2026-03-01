export interface PublicCompany {
    uuid: string;
    public_company_name: string;
    address: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    unit: string | null;
    user_id: number | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface PublicCompanyFilters {
    search?: string;
    dateFrom?: string;
    dateTo?: string;
    status?: string | undefined;
    onlyTrashed?: boolean;
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    page?: number;
    perPage?: number;
}
