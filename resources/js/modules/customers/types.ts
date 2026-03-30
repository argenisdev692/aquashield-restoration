export interface Customer {
    uuid: string;
    name: string;
    last_name: string | null;
    email: string;
    cell_phone: string | null;
    home_phone: string | null;
    occupation: string | null;
    user_id: number;
    user_name: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface CustomerListItem {
    customer_id: number;
    uuid: string;
    name: string;
    last_name: string | null;
    email: string;
    cell_phone: string | null;
    home_phone: string | null;
    occupation: string | null;
    user_id: number;
    user_name: string | null;
    created_at: string;
    deleted_at: string | null;
}

export interface CustomerFilters {
    search?: string;
    status?: 'active' | 'deleted';
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface CustomerFormData {
    name: string;
    last_name: string | null;
    email: string;
    cell_phone: string | null;
    home_phone: string | null;
    occupation: string | null;
    user_id: number;
}

export interface PaginatedCustomerResponse {
    data: CustomerListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
