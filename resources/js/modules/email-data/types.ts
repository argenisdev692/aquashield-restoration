export interface EmailData {
    uuid: string;
    description: string | null;
    email: string;
    phone: string | null;
    type: string | null;
    user_id: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface EmailDataListItem {
    uuid: string;
    description: string | null;
    email: string;
    phone: string | null;
    type: string | null;
    user_id: number;
    created_at: string;
    deleted_at: string | null;
}

export interface EmailDataFilters {
    search?: string;
    type?: string;
    status?: "active" | "deleted";
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface EmailDataFormData {
    description: string;
    email: string;
    phone: string;
    type: string;
}

export interface PaginatedEmailDataResponse {
    data: EmailDataListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
