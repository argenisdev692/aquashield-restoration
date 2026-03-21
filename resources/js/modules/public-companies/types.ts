export interface PublicCompany {
    uuid: string;
    public_company_name: string;
    address: string | null;
    address_2: string | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    unit: string | null;
    user_id: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface PublicCompanyFormData {
    public_company_name: string;
    address: string;
    phone: string;
    email: string;
    website: string;
    unit: string;
}

export interface PublicCompanyFilters {
    search?: string;
    status?: 'active' | 'deleted';
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export type CreatePublicCompanyPayload = PublicCompanyFormData & { address_2: string };
export type UpdatePublicCompanyPayload = PublicCompanyFormData & { address_2: string };

export type PublicCompanyFormErrors = Partial<Record<keyof PublicCompanyFormData, string>>;
