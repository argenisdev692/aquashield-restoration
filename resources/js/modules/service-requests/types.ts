export interface ServiceRequest {
    uuid: string;
    requested_service: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface ServiceRequestListItem {
    uuid: string;
    requested_service: string;
    created_at: string;
    deleted_at: string | null;
}

export interface ServiceRequestFilters {
    search?: string;
    status?: "active" | "deleted";
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface ServiceRequestFormData {
    requested_service: string;
}

export interface PaginatedServiceRequestResponse {
    data: ServiceRequestListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
