export interface AllianceCompany {
    uuid: string;
    alliance_company_name: string;
    address: string | null;
    address_2?: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    user_id: number | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface AllianceCompanyFilters {
    search?: string;
    dateFrom?: string;
    dateTo?: string;
    onlyTrashed?: boolean;
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    page?: number;
    perPage?: number;
    status?: string | undefined;
}
