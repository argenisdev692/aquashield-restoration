export interface ContactSupport {
    uuid: string;
    full_name: string;
    first_name: string;
    last_name: string | null;
    email: string;
    phone: string | null;
    message: string;
    sms_consent: boolean;
    readed: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface ContactSupportListItem {
    uuid: string;
    full_name: string;
    email: string;
    phone: string | null;
    sms_consent: boolean;
    readed: boolean;
    created_at: string;
    deleted_at: string | null;
}

export interface ContactSupportFilters {
    search?: string;
    read_state?: "read" | "unread";
    status?: "active" | "deleted";
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface ContactSupportFormData {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    message: string;
    sms_consent: boolean;
    readed: boolean;
}

export interface PaginatedContactSupportResponse {
    data: ContactSupportListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
