export interface InsuranceCompany {
    company_id: number;
    uuid: string;
    insurance_company_name: string;
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

export interface InsuranceCompanyFormData {
    insurance_company_name: string;
    address: string;
    address_2: string;
    phone: string;
    email: string;
    website: string;
}

export interface InsuranceCompanyFilters {
    search?: string;
    status?: 'active' | 'deleted';
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export type CreateInsuranceCompanyPayload = InsuranceCompanyFormData;
export type UpdateInsuranceCompanyPayload = InsuranceCompanyFormData;

export type InsuranceCompanyFormErrors = Partial<Record<keyof InsuranceCompanyFormData, string>>;
